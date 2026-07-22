<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Carrier;
use App\Services\CarrierSelectorService;
use App\Services\ImeiReservationService;
use App\Services\ShippingService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentWebhookService
{
    public function __construct(private ImeiReservationService $imeiService, private ShippingService $shippingService, private CarrierSelectorService $carrierSelector) {}

    /**
     * Verify signature for known gateways. Returns true if valid or no secret configured.
     */
    protected function verifySignature(string $gateway, Request $request): bool
    {
        $signature = $request->input('signature') ?? $request->header('X-Signature');

        if (! $signature) {
            // No signature provided; allow in local/dev but log.
            Log::warning('Webhook without signature for gateway: ' . $gateway);
            return app()->environment('production') ? false : true;
        }

        $secretKey = match (strtolower($gateway)) {
            'momo' => env('MOMO_SECRET'),
            'vnpay' => env('VNPAY_SECRET'),
            'zalopay' => env('ZALOPAY_SECRET'),
            default => null,
        };

        if (! $secretKey) {
            // no configured secret — allow but log
            Log::info("No secret configured for gateway {$gateway}; skipping signature verification.");
            return true;
        }

        $payload = $request->getContent();
        $computed = hash_hmac('sha256', $payload, $secretKey);

        return hash_equals($computed, $signature);
    }

    /**
     * Process incoming webhook. Returns array [statusCode, message]
     */
    public function handle(Request $request): array
    {
        $gateway = $request->input('gateway') ?? $request->header('X-Gateway') ?? 'unknown';

        if (! $this->verifySignature($gateway, $request)) {
            Log::warning('Webhook signature verification failed', ['gateway' => $gateway]);
            return [403, 'Invalid signature'];
        }

        $transaction = $request->input('transaction_code');
        $paymentId = $request->input('payment_id');
        $status = strtolower($request->input('status', 'failed'));

        $payment = null;
        if ($transaction) {
            $payment = Payment::where('transaction_code', $transaction)->first();
        }

        if (! $payment && $paymentId) {
            $payment = Payment::find($paymentId);
        }

        if (! $payment) {
            Log::warning('Webhook payment not found', ['transaction' => $transaction, 'payment_id' => $paymentId]);
            return [404, 'Payment not found'];
        }

        // Idempotency: if already processed, return OK
        if ($payment->payment_status === 'paid' && $status === 'success') {
            return [200, 'Already processed'];
        }

        if ($status === 'success') {
            $this->markPaid($payment, $transaction);

            return [200, 'OK'];
        }

        // failure
        $payment->payment_status = 'failed';
        $payment->transaction_code = $transaction ?? $payment->transaction_code;
        $payment->save();

        $this->imeiService->release($payment->order);

        return [200, 'Failed'];
    }

    /**
     * Webhook biến động số dư ngân hàng kiểu SePay/Casso: mỗi khi có tiền vào tài khoản shop,
     * dịch vụ trung gian POST về đây kèm nội dung chuyển khoản. Ta đối chiếu mã đơn hàng (order_code,
     * đã lưu sẵn ở payments.transaction_code khi tạo phiên bank_transfer) có xuất hiện trong nội dung
     * hay không, và số tiền chuyển vào có khớp với số tiền đơn hàng hay không. Khớp cả hai mới tự động
     * xác nhận — nếu không sẽ bỏ qua để admin đối soát thủ công như trước (an toàn, tránh cộng nhầm tiền).
     *
     * Payload tham khảo định dạng SePay: { content, transferAmount, transferType, referenceCode, ... }
     */
    public function handleBankTransfer(Request $request): array
    {
        $configuredToken = config('services.sepay.webhook_token');
        $providedToken = str_replace('Apikey ', '', (string) $request->header('Authorization'));

        if ($configuredToken && ! hash_equals($configuredToken, $providedToken)) {
            Log::warning('SePay webhook: invalid token');
            return [401, 'Invalid token'];
        }

        $transferType = strtolower((string) $request->input('transferType', 'in'));
        if ($transferType !== 'in') {
            // Tiền ra khỏi tài khoản (hoàn tiền thủ công...) không liên quan đến xác nhận đơn hàng.
            return [200, 'Ignored (not incoming transfer)'];
        }

        $content = (string) $request->input('content', $request->input('description', ''));
        $amount = (float) $request->input('transferAmount', 0);
        $referenceCode = (string) $request->input('referenceCode', $request->input('id', ''));

        if (! preg_match('/[A-Z0-9]{6,}/i', $content, $matches)) {
            Log::info('SePay webhook: no order code pattern found in content', ['content' => $content]);
            return [200, 'No order code found'];
        }

        // Nội dung chuyển khoản có thể lẫn text khác (tên ngân hàng thêm tiền tố/hậu tố), nên
        // tìm payment có transaction_code (order_code) xuất hiện như một chuỗi con của content,
        // thay vì so khớp tuyệt đối.
        $payment = Payment::query()
            ->where('payment_method', 'bank_transfer')
            ->where('payment_status', 'pending')
            ->get()
            ->first(fn (Payment $p) => $p->transaction_code && stripos($content, $p->transaction_code) !== false);

        if (! $payment) {
            Log::info('SePay webhook: no matching pending bank_transfer payment', ['content' => $content]);
            return [200, 'No matching payment'];
        }

        if ($payment->isExpired()) {
            Log::info('SePay webhook: matched payment already expired', ['payment_id' => $payment->id]);
            return [200, 'Payment expired'];
        }

        // Số tiền phải khớp đúng (cho phép sai số làm tròn 1đ) để tránh xác nhận nhầm khi khách
        // chuyển thiếu/thừa hoặc nội dung trùng ngẫu nhiên với đơn khác.
        if (abs($amount - (float) $payment->amount) > 1) {
            Log::warning('SePay webhook: amount mismatch, falling back to manual review', [
                'payment_id' => $payment->id,
                'expected' => $payment->amount,
                'received' => $amount,
            ]);
            return [200, 'Amount mismatch — left for manual review'];
        }

        $this->markPaid($payment, $referenceCode ?: $payment->transaction_code);

        return [200, 'OK'];
    }

    /**
     * Mô phỏng ngân hàng báo có tiền: dùng cho demo đồ án, được gọi khi đã quá thời điểm
     * simulate_confirm_at của payment (xem CheckoutController::paymentStatus). Xử lý y hệt
     * một webhook ngân hàng thật (markPaid) để hành vi ứng dụng giống thực tế.
     */
    public function confirmSimulatedBankTransfer(Payment $payment): void
    {
        $this->markPaid($payment, $payment->transaction_code);
    }

    /**
     * Đánh dấu payment đã thanh toán, chuyển đơn sang xử lý và tạo vận đơn — dùng chung cho
     * webhook cổng thanh toán (momo/vnpay/zalopay) và webhook ngân hàng (SePay).
     */
    private function markPaid(Payment $payment, ?string $transactionCode): void
    {
        $payment->payment_status = 'paid';
        $payment->paid_at = Carbon::now();
        $payment->transaction_code = $transactionCode ?? $payment->transaction_code;
        $payment->save();

        $this->imeiService->finalize($payment->order);

        $order = $payment->order;
        $order->status = 'processing';
        $order->save();

        try {
            $carrier = $this->carrierSelector->selectForOrder($order);
            if ($carrier) {
                $this->shippingService->createShipment($order, $carrier, ['source' => 'auto']);
            } else {
                Log::info('No active carrier found; skipping shipment creation for order ' . $order->id);
            }
        } catch (\Exception $e) {
            if (app()->environment('testing')) {
                throw $e;
            }

            Log::error('Error creating shipment for order ' . $order->id . ': ' . $e->getMessage());
        }
    }
}

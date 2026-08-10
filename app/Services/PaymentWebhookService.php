<?php

namespace App\Services;

use Illuminate\Http\Request;
<<<<<<< HEAD
use App\Models\BankAccount;
use App\Models\Payment;
use App\Models\WalletTopup;
use App\Services\CarrierSelectorService;
use App\Services\ImeiReservationService;
use App\Services\ShippingService;
use App\Services\WalletService;
use App\Services\WalletWithdrawalService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
=======
use App\Models\Payment;
use App\Models\Carrier;
use App\Services\CarrierSelectorService;
use App\Services\ImeiReservationService;
use App\Services\ShippingService;
use Illuminate\Support\Facades\Log;
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
use Carbon\Carbon;

class PaymentWebhookService
{
<<<<<<< HEAD
    public function __construct(
        private ImeiReservationService $imeiService,
        private ShippingService $shippingService,
        private CarrierSelectorService $carrierSelector,
        private ReceiptImageService $receiptImageService,
        private WalletService $walletService,
        private WalletWithdrawalService $walletWithdrawalService,
    ) {}
=======
    public function __construct(private ImeiReservationService $imeiService, private ShippingService $shippingService, private CarrierSelectorService $carrierSelector) {}
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706

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
<<<<<<< HEAD
=======
            'momo' => env('MOMO_SECRET'),
            'vnpay' => env('VNPAY_SECRET'),
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
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
<<<<<<< HEAD
            $this->markPaid($payment, $transaction);
=======
            $payment->payment_status = 'paid';
            $payment->paid_at = Carbon::now();
            $payment->transaction_code = $transaction ?? $payment->transaction_code;
            $payment->save();

            $this->imeiService->finalize($payment->order);

            // mark order as processing
            $order = $payment->order;
            $order->status = 'processing';
            $order->save();

            // create shipment using default carrier if available
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
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706

            return [200, 'OK'];
        }

        // failure
        $payment->payment_status = 'failed';
        $payment->transaction_code = $transaction ?? $payment->transaction_code;
        $payment->save();

        $this->imeiService->release($payment->order);

        return [200, 'Failed'];
    }
<<<<<<< HEAD

    /**
     * Webhook biến động số dư ngân hàng kiểu SePay/Casso: mỗi khi có tiền vào tài khoản shop,
     * dịch vụ trung gian POST về đây kèm nội dung chuyển khoản. Ta đối chiếu mã đơn hàng (order_code,
     * đã lưu sẵn ở payments.transaction_code khi tạo phiên bank_transfer/vietqr) có xuất hiện trong
     * nội dung hay không, và số tiền chuyển vào có khớp với số tiền đơn hàng hay không. Khớp cả hai mới
     * tự động xác nhận — nếu không sẽ bỏ qua để admin đối soát thủ công như trước (an toàn, tránh cộng
     * nhầm tiền). Dùng chung cho cả "Chuyển khoản ngân hàng" và "VietQR" vì cả hai đều là chuyển khoản
     * thật vào cùng 1 tài khoản ngân hàng, chỉ khác giao diện QR hiển thị.
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
        $content = (string) $request->input('content', $request->input('description', ''));
        $amount = (float) $request->input('transferAmount', 0);
        $referenceCode = (string) $request->input('referenceCode', $request->input('id', ''));

        if ($transferType !== 'in') {
            // Tiền ra khỏi tài khoản cửa hàng — chỉ quan tâm nếu khớp với 1 yêu cầu rút tiền đang
            // chờ admin quét QR chuyển khoản thật (WalletWithdrawalService::confirmSepayPayout).
            return $this->handlePayoutTransfer($content, $amount, $referenceCode);
        }

        if (! preg_match('/[A-Z0-9]{6,}/i', $content, $matches)) {
            Log::info('SePay webhook: no order code pattern found in content', ['content' => $content]);
            return [200, 'No order code found'];
        }

        // Nội dung chuyển khoản có thể lẫn text khác (tên ngân hàng thêm tiền tố/hậu tố), nên
        // tìm payment có transaction_code (order_code) xuất hiện như một chuỗi con của content,
        // thay vì so khớp tuyệt đối.
        $payment = Payment::query()
            ->whereIn('payment_method', ['bank_transfer', 'vietqr'])
            ->where('payment_status', 'pending')
            ->get()
            ->first(fn (Payment $p) => $p->transaction_code && stripos($content, $p->transaction_code) !== false);

        if ($payment) {
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

        // Không khớp đơn hàng nào — thử tìm yêu cầu nạp ví (transaction_code dạng "NAPVI...",
        // xem WalletService::initiateTopup) theo đúng cơ chế đối chiếu nội dung + số tiền như trên.
        $topup = WalletTopup::query()
            ->whereIn('payment_method', ['bank_transfer', 'vietqr'])
            ->where('payment_status', 'pending')
            ->get()
            ->first(fn (WalletTopup $t) => $t->transaction_code && stripos($content, $t->transaction_code) !== false);

        if (! $topup) {
            Log::info('SePay webhook: no matching pending payment/topup', ['content' => $content]);
            return [200, 'No matching payment'];
        }

        if ($topup->isExpired()) {
            Log::info('SePay webhook: matched topup already expired', ['topup_id' => $topup->id]);
            return [200, 'Topup expired'];
        }

        if (abs($amount - (float) $topup->amount) > 1) {
            Log::warning('SePay webhook: topup amount mismatch, falling back to manual review', [
                'topup_id' => $topup->id,
                'expected' => $topup->amount,
                'received' => $amount,
            ]);
            return [200, 'Amount mismatch — left for manual review'];
        }

        $this->walletService->confirmSepayTopup($topup, $referenceCode ?: null);

        return [200, 'OK'];
    }

    /**
     * Xử lý giao dịch "tiền ra" SePay báo về — chỉ dùng để tự động hoàn tất yêu cầu rút tiền khi
     * admin đã quét QR (vietQrPayoutUrl) chuyển khoản thật cho khách. Ưu tiên đối chiếu theo
     * transaction_code "RUT..." xuất hiện trong nội dung; nếu app ngân hàng của admin không giữ
     * lại đúng nội dung QR (một số app tự thay bằng mô tả mặc định kiểu "X chuyen tien"), rơi về
     * khớp theo SỐ TIỀN khi chỉ có đúng 1 yêu cầu đang chờ — an toàn vì tiền ra luôn do admin cửa
     * hàng chủ động khởi tạo (không phải nhiều khách gửi ngẫu nhiên như tiền vào).
     */
    private function handlePayoutTransfer(string $content, float $amount, string $referenceCode): array
    {
        $pendingWithdrawals = \App\Models\WalletWithdrawal::query()
            ->whereIn('status', ['pending', 'processing'])
            ->get();

        $withdrawal = $pendingWithdrawals->first(fn ($w) => $w->transaction_code && stripos($content, $w->transaction_code) !== false);

        if (! $withdrawal) {
            $amountMatches = $pendingWithdrawals->filter(fn ($w) => abs($amount - (float) $w->amount) <= 1);

            if ($amountMatches->count() === 1) {
                $withdrawal = $amountMatches->first();
                Log::info('SePay webhook: matched outgoing transfer by amount fallback (content had no reference code)', [
                    'withdrawal_id' => $withdrawal->id,
                    'content' => $content,
                ]);
            }
        }

        if (! $withdrawal) {
            Log::info('SePay webhook: no matching pending withdrawal for outgoing transfer', ['content' => $content, 'amount' => $amount]);
            return [200, 'No matching withdrawal'];
        }

        if (abs($amount - (float) $withdrawal->amount) > 1) {
            Log::warning('SePay webhook: withdrawal amount mismatch, left for manual admin confirmation', [
                'withdrawal_id' => $withdrawal->id,
                'expected' => $withdrawal->amount,
                'received' => $amount,
            ]);
            return [200, 'Amount mismatch — left for manual review'];
        }

        $this->walletWithdrawalService->confirmSepayPayout($withdrawal, $referenceCode ?: null);

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
     * webhook cổng thanh toán (zalopay) và webhook ngân hàng SePay (bank_transfer/vietqr).
     */
    private function markPaid(Payment $payment, ?string $transactionCode): void
    {
        $payment->payment_status = 'paid';
        $payment->paid_at = Carbon::now();
        $payment->transaction_code = $transactionCode
            ?? $payment->transaction_code
            ?? strtoupper($payment->payment_method) . strtoupper(Str::random(10));

        // Webhook/mô phỏng ngân hàng không tự có tên chủ tài khoản chuyển khoản; dùng tên khách
        // đứng đơn để hiển thị trên hóa đơn, giống cách sao kê ngân hàng thật hiện tên người gửi.
        if (! $payment->payer_name) {
            $payerName = $payment->order->user->name ?? $payment->order->customer_name ?? null;
            if ($payerName) {
                $payment->payer_name = Str::upper(BankAccount::normalizeName($payerName));
            }
        }

        // Quét QR tự động xác nhận thì không có ảnh chụp màn hình khách gửi; tự vẽ một ảnh hóa
        // đơn làm chứng từ lưu lại, giống vai trò của proof_image trong luồng đối soát thủ công.
        if (! $payment->proof_image) {
            $payment->proof_image = $this->receiptImageService->generate(
                'GIAO DICH THANH CONG',
                number_format((float) $payment->amount, 0, ',', '.') . ' VND',
                $this->brandColor($payment->payment_method),
                [
                    ['Don vi thu huong', config('services.sepay.account_name')],
                    ['Tai khoan thu huong', (string) config('services.sepay.account_number')],
                    ['Nguoi chuyen', (string) $payment->payer_name],
                    ['Ma giao dich', (string) $payment->transaction_code],
                    ['Noi dung', (string) $payment->order->order_code],
                    ['Thoi gian', $payment->paid_at->format('H:i:s d/m/Y')],
                ]
            );
        }

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

    /**
     * @return array{r:int,g:int,b:int}
     */
    private function brandColor(string $method): array
    {
        return match ($method) {
            'vietqr' => ['r' => 0, 'g' => 160, 'b' => 227],
            default => ['r' => 0, 'g' => 122, 'b' => 77],
        };
    }
=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
}

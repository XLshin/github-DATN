<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\MomoService;
use App\Services\PaymentWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MomoController extends Controller
{
    public function __construct(
        private readonly MomoService $momoService,
    ) {}

    /**
     * MoMo gọi POST về server (IPN) để báo kết quả giao dịch — đây là nguồn xác nhận
     * đáng tin cậy duy nhất, không phụ thuộc vào việc khách có quay lại returnUrl hay không.
     */
    public function ipn(Request $request, PaymentWebhookService $webhookService)
    {
        $data = $request->all();

        if (! $this->momoService->verifySignature($data)) {
            Log::warning('MoMo IPN: sai chữ ký', $data);
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $payment = Payment::find((int) ($data['extraData'] ?? 0));

        if (! $payment || $payment->payment_method !== 'momo') {
            Log::warning('MoMo IPN: không tìm thấy giao dịch', $data);
            return response()->json(['message' => 'Payment not found'], 404);
        }

        if ($payment->payment_status === 'paid') {
            return response()->json(['message' => 'Already processed']);
        }

        if ((int) ($data['resultCode'] ?? -1) === 0) {
            $webhookService->confirmMomoPayment($payment, $data['transId'] ?? null);
        } else {
            $payment->update([
                'payment_status' => 'failed',
                'payer_note' => 'Giao dịch MoMo thất bại: ' . ($data['message'] ?? 'Không rõ lý do'),
            ]);
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * Khách được MoMo redirect (GET) về sau khi thanh toán xong. Chỉ dùng để điều hướng UI —
     * trạng thái thanh toán thật lấy từ IPN (ipn() ở trên), không tin trực tiếp resultCode ở đây.
     */
    public function returnUrl(Request $request)
    {
        $payment = Payment::find((int) $request->query('extraData', 0));

        if (! $payment) {
            return redirect()->route('checkout.show')->with('error', 'Không tìm thấy giao dịch MoMo.');
        }

        return redirect()->route('checkout.success', $payment->order_id);
    }
}

<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Tích hợp cổng thanh toán MoMo (môi trường Test/Sandbox) theo tài liệu MoMo
 * "Thanh toán qua Cổng thanh toán" (captureWallet): tạo giao dịch, nhận payUrl để
 * redirect khách sang MoMo thật, và xác thực chữ ký IPN khi MoMo báo kết quả về.
 */
class MomoService
{
    /**
     * @return array{pay_url: string, request_id: string, order_id: string}
     */
    public function createPayment(Order $order, Payment $payment): array
    {
        $partnerCode = config('services.momo.partner_code');
        $accessKey = config('services.momo.access_key');
        $secretKey = config('services.momo.secret_key');

        $requestId = $partnerCode . '_' . $payment->id . '_' . time();
        $momoOrderId = $requestId;
        $amount = (string) (int) round((float) $payment->amount);
        $orderInfo = 'Thanh toan don hang ' . $order->order_code;
        $redirectUrl = config('services.momo.return_url');
        $ipnUrl = config('services.momo.ipn_url');
        $requestType = 'captureWallet';
        // extraData mang theo payment_id để IPN/return đối chiếu ngược lại đúng giao dịch nội bộ.
        $extraData = (string) $payment->id;

        $rawSignature = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}"
            . "&ipnUrl={$ipnUrl}&orderId={$momoOrderId}&orderInfo={$orderInfo}"
            . "&partnerCode={$partnerCode}&redirectUrl={$redirectUrl}&requestId={$requestId}&requestType={$requestType}";

        $signature = hash_hmac('sha256', $rawSignature, $secretKey);

        $response = Http::timeout(30)->post(config('services.momo.endpoint'), [
            'partnerCode' => $partnerCode,
            'accessKey'   => $accessKey,
            'requestId'   => $requestId,
            'amount'      => $amount,
            'orderId'     => $momoOrderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl'      => $ipnUrl,
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => $signature,
            'lang'        => 'vi',
        ]);

        $data = $response->json() ?? [];

        if ((int) ($data['resultCode'] ?? -1) !== 0 || empty($data['payUrl'])) {
            Log::error('MoMo createPayment thất bại', ['response' => $data]);
            throw new \RuntimeException($data['message'] ?? 'Không thể khởi tạo giao dịch MoMo.');
        }

        return [
            'pay_url'    => $data['payUrl'],
            'request_id' => $requestId,
            'order_id'   => $momoOrderId,
        ];
    }

    /**
     * Xác thực chữ ký HMAC-SHA256 của IPN/redirect MoMo gửi về, theo đúng thứ tự field MoMo quy định.
     */
    public function verifySignature(array $data): bool
    {
        if (empty($data['signature'])) {
            return false;
        }

        $rawSignature = "accessKey=" . config('services.momo.access_key')
            . "&amount=" . ($data['amount'] ?? '')
            . "&extraData=" . ($data['extraData'] ?? '')
            . "&message=" . ($data['message'] ?? '')
            . "&orderId=" . ($data['orderId'] ?? '')
            . "&orderInfo=" . ($data['orderInfo'] ?? '')
            . "&orderType=" . ($data['orderType'] ?? '')
            . "&partnerCode=" . ($data['partnerCode'] ?? '')
            . "&payType=" . ($data['payType'] ?? '')
            . "&requestId=" . ($data['requestId'] ?? '')
            . "&responseTime=" . ($data['responseTime'] ?? '')
            . "&resultCode=" . ($data['resultCode'] ?? '')
            . "&transId=" . ($data['transId'] ?? '');

        $computed = hash_hmac('sha256', $rawSignature, config('services.momo.secret_key'));

        return hash_equals($computed, (string) $data['signature']);
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Giả lập lời gọi IPN thật mà MoMo gửi về sau khi thanh toán, để test toàn bộ luồng kỹ thuật
 * (xác thực chữ ký, xác nhận đơn hàng, tạo vận đơn...) khi chưa có tài khoản M4B riêng để hoàn
 * tất giao dịch bằng app MoMo thật. Chữ ký được tính đúng công thức HMAC-SHA256 của MoMo, dùng
 * chung secret key test — MomoController::ipn() không phân biệt được đây là gọi thật hay giả lập.
 */
class SimulateMomoIpn extends Command
{
    protected $signature = 'momo:simulate-ipn {payment_id} {--fail}';

    protected $description = 'Giả lập MoMo gửi IPN xác nhận thanh toán về server (dùng khi test local, chưa có tài khoản M4B thật)';

    public function handle(): int
    {
        $payment = Payment::find((int) $this->argument('payment_id'));

        if (! $payment) {
            $this->error('Không tìm thấy payment id ' . $this->argument('payment_id'));
            return self::FAILURE;
        }

        if ($payment->payment_method !== 'momo') {
            $this->error('Payment này không phải phương thức momo.');
            return self::FAILURE;
        }

        $fail = (bool) $this->option('fail');

        $accessKey = config('services.momo.access_key');
        $secretKey = config('services.momo.secret_key');
        $partnerCode = config('services.momo.partner_code');

        $data = [
            'partnerCode' => $partnerCode,
            'orderId' => $partnerCode . '_' . $payment->id . '_simulated',
            'requestId' => $partnerCode . '_' . $payment->id . '_simulated',
            'amount' => (string) (int) round((float) $payment->amount),
            'orderInfo' => 'Thanh toan don hang ' . $payment->order->order_code,
            'orderType' => 'momo_wallet',
            'transId' => (string) random_int(1000000000, 9999999999),
            'resultCode' => $fail ? 1006 : 0,
            'message' => $fail ? 'Giao dịch bị từ chối bởi người dùng (giả lập)' : 'Thành công (giả lập)',
            'payType' => 'qr',
            'responseTime' => (string) round(microtime(true) * 1000),
            'extraData' => (string) $payment->id,
        ];

        $rawSignature = "accessKey={$accessKey}&amount={$data['amount']}&extraData={$data['extraData']}"
            . "&message={$data['message']}&orderId={$data['orderId']}&orderInfo={$data['orderInfo']}"
            . "&orderType={$data['orderType']}&partnerCode={$data['partnerCode']}&payType={$data['payType']}"
            . "&requestId={$data['requestId']}&responseTime={$data['responseTime']}&resultCode={$data['resultCode']}"
            . "&transId={$data['transId']}";

        $data['signature'] = hash_hmac('sha256', $rawSignature, $secretKey);

        $response = Http::post(url('/webhook/momo/ipn'), $data);

        $this->info('HTTP ' . $response->status() . ': ' . $response->body());

        $payment->refresh();
        $this->info('Trạng thái payment sau khi gọi: ' . $payment->payment_status);

        return self::SUCCESS;
    }
}

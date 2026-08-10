<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentWebhookService;

class WebhookController extends Controller
{
    public function paymentCallback(Request $request, PaymentWebhookService $service)
    {
        [$code, $message] = $service->handle($request);
        return response()->json(['message' => $message], $code);
    }
<<<<<<< HEAD

    /**
     * Webhook biến động số dư ngân hàng (SePay/Casso) — tự động xác nhận thanh toán chuyển khoản.
     */
    public function bankTransferCallback(Request $request, PaymentWebhookService $service)
    {
        [$code, $message] = $service->handleBankTransfer($request);
        return response()->json(['success' => $code === 200, 'message' => $message], $code);
    }
=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
}

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

    /**
     * Webhook biến động số dư ngân hàng (SePay/Casso) — tự động xác nhận thanh toán chuyển khoản.
     */
    public function bankTransferCallback(Request $request, PaymentWebhookService $service)
    {
        [$code, $message] = $service->handleBankTransfer($request);
        return response()->json(['success' => $code === 200, 'message' => $message], $code);
    }
}

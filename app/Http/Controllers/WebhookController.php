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
}

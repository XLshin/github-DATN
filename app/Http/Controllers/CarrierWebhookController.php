<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carrier;
use App\Services\CarrierWebhookService;

class CarrierWebhookController extends Controller
{
    public function handle(Request $request, string $code, CarrierWebhookService $service)
    {
        $carrier = Carrier::where('code', $code)->first();
        if (! $carrier) {
            return response('Carrier not found', 404);
        }

        [$status, $message] = $service->handle($carrier, $request);

        return response($message, $status);
    }
}

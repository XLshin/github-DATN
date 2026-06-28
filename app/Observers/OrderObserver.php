<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\CarrierSelectorService;
use App\Services\ShippingService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $new = $order->status;

        if (! in_array($new, ['processing', 'shipping'])) {
            return;
        }

        // Only create shipment if none exists
        if ($order->shipment()->exists()) {
            return;
        }

        try {
            $carrierSelector = app(CarrierSelectorService::class);
            $shippingService = app(ShippingService::class);

            $carrier = $carrierSelector->selectForOrder($order);
            if ($carrier) {
                $shippingService->createShipment($order, $carrier, ['source' => 'auto']);
                Log::info('Auto-created shipment for order via OrderObserver', ['order_id' => $order->id]);
            } else {
                Log::info('No carrier available for auto shipment', ['order_id' => $order->id]);
            }
        } catch (\Exception $e) {
            Log::error('OrderObserver failed to auto-create shipment: ' . $e->getMessage(), ['order_id' => $order->id]);
        }
    }
}

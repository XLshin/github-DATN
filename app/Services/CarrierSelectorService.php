<?php

namespace App\Services;

use App\Models\Carrier;
use App\Models\Order;

class CarrierSelectorService
{
    /**
     * Select the best carrier for an order using simple rules:
     * - prefer carriers with matching zones declared in `api_credentials.zones`
     * - then by `api_credentials.priority` if present (lower is better)
     * - otherwise first active carrier
     */
    public function selectForOrder(Order $order): ?Carrier
    {
        $carriers = Carrier::where('active', true)->get();

        if ($carriers->isEmpty()) {
            return null;
        }

        $address = strtolower($order->shipping_address ?? '');

        // Try zone match
        $zoneMatch = null;
        foreach ($carriers as $carrier) {
            $creds = $carrier->api_credentials ?? [];
            $zones = $creds['zones'] ?? null;
            if (! $zones) continue;

            foreach ((array) $zones as $zone) {
                if ($zone && strpos($address, strtolower($zone)) !== false) {
                    $zoneMatch = $carrier;
                    break 2;
                }
            }
        }

        if ($zoneMatch) {
            return $zoneMatch;
        }

        // Sort by explicit priority if provided
        $sorted = $carriers->sortBy(function (Carrier $c) {
            $creds = $c->api_credentials ?? [];
            return isset($creds['priority']) ? (int) $creds['priority'] : 9999;
        });

        return $sorted->first();
    }
}

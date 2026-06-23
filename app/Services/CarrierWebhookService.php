<?php

namespace App\Services;

use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CarrierWebhookService
{
    public function __construct(private ShippingService $shippingService) {}

    public function handle(Carrier $carrier, Request $request): array
    {
        // verify signature if configured
        $signature = $request->header('X-Signature') ?? $request->input('signature');
        if ($carrier->webhook_secret && $signature) {
            $computed = hash_hmac('sha256', $request->getContent(), $carrier->webhook_secret);
            if (! hash_equals($computed, $signature)) {
                Log::warning('Carrier webhook signature mismatch', ['carrier' => $carrier->code]);
                return [403, 'Invalid signature'];
            }
        }

        $payload = $request->all();

        // find shipment by tracking_code or shipment_code or order_code
        $tracking = $payload['tracking_code'] ?? $payload['shipment_code'] ?? $payload['tracking'] ?? null;

        if (! $tracking && isset($payload['order_code'])) {
            $shipment = \App\Models\Shipment::whereHas('order', function ($q) use ($payload) {
                $q->where('order_code', $payload['order_code']);
            })->first();
        } else {
            $shipment = \App\Models\Shipment::where('tracking_code', $tracking)
                ->orWhere('shipment_code', $tracking)
                ->first();
        }

        if (! $shipment) {
            Log::warning('Carrier webhook: shipment not found', ['payload' => $payload]);
            return [404, 'Shipment not found'];
        }

        $status = strtolower($payload['status'] ?? $payload['shipping_status'] ?? '');
        // normalize to our shipping_status values
        $map = [
            'picked_up' => 'shipping',
            'in_transit' => 'shipping',
            'delivered' => 'delivered',
            'failed' => 'failed',
            'returned' => 'failed',
        ];

        $newStatus = $map[$status] ?? ($map[$payload['status'] ?? ''] ?? null);
        if (! $newStatus) {
            // unknown status, log and accept
            Log::info('Carrier webhook: unknown status', ['status' => $status, 'payload' => $payload]);
            return [200, 'Ignored'];
        }

        try {
            $this->shippingService->updateShipmentStatus($shipment, $newStatus, $payload);
        } catch (\Exception $e) {
            Log::error('Carrier webhook processing failed: ' . $e->getMessage(), ['shipment_id' => $shipment->id]);
            return [500, 'Error'];
        }

        return [200, 'OK'];
    }
}

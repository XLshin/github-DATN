<?php

namespace App\Services;

use App\Models\Carrier;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use Illuminate\Support\Facades\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class ShippingService
{
    public function createShipment(Order $order, Carrier $carrier, array $options = []): Shipment
    {
        $data = ['order_id' => $order->id, 'requested_at' => now()];

        if (Schema::hasColumn('shipments', 'carrier_id')) {
            $data['carrier_id'] = $carrier->id;
        }

        if (Schema::hasColumn('shipments', 'shipping_unit')) {
            $data['shipping_unit'] = $carrier->name;
        }

        if (Schema::hasColumn('shipments', 'status')) {
            $data['status'] = 'pending';
        }

        if (Schema::hasColumn('shipments', 'shipping_status')) {
            $data['shipping_status'] = 'pending';
        }

        if (Schema::hasColumn('shipments', 'metadata')) {
            $data['metadata'] = Arr::except($options, ['label']);
        }

        $shipment = Shipment::create($data);

        foreach ($order->items as $item) {
            ShipmentItem::create([
                'shipment_id' => $shipment->id,
                'order_item_id' => $item->id,
                'quantity' => $item->quantity,
            ]);
        }

        try {
            $shipment->shipment_code = 'CARRIER-' . strtoupper(uniqid());
            $shipment->tracking_url = 'https://tracking.example/' . $shipment->shipment_code;
            $shipment->status = 'label_created';
            $shipment->save();

            Log::info('Shipment label created', ['shipment_id' => $shipment->id]);

            $this->notifyShipmentUpdate($shipment, 'label_created');
        } catch (\Exception $e) {
            Log::error('Failed to create carrier label: ' . $e->getMessage());
            $shipment->status = 'failed';
            $shipment->save();
        }

        return $shipment;
    }

    public function updateShipmentStatus(Shipment $shipment, string $newStatus, array $meta = []): void
    {
        DB::transaction(function () use ($shipment, $newStatus, $meta) {
            $data = [];

            if (
                $newStatus === 'shipping'
                && Schema::hasColumn('shipments', 'shipped_at')
                && is_null($shipment->shipped_at)
            ) {
                $data['shipped_at'] = now();
            }

            if ($newStatus === 'delivered') {
                $data['delivered_at'] = now();
            }

            $data['shipping_status'] = $newStatus;

            $shipment->update($data + [
                'metadata' => array_merge($shipment->metadata ?? [], $meta),
            ]);

            if ($newStatus === 'delivered') {
                $shipment->order->update(['status' => 'completed']);

                return;
            }

            if ($newStatus === 'failed') {
                $shipment->order->loadMissing('items.product', 'items.variant', 'items.imeis');

                foreach ($shipment->order->items as $item) {
                    $returnedImeis = 0;

                    foreach ($item->imeis as $imei) {
                        if ($imei->status === 'reserved') {
                            $imei->releaseReservation();
                            $returnedImeis++;
                        }
                    }

                    if ($returnedImeis > 0) {
                        InventoryTransaction::create([
                            'product_variant_id' => $item->product_variant_id,
                            'type' => 'return',
                            'quantity' => $returnedImeis,
                            'note' => 'Trả IMEI về kho do giao thất bại: ' . $shipment->order->order_code,
                        ]);
                    }

                    if (
                        $item->product &&
                        $item->product->product_type === 'quantity' &&
                        $item->variant
                    ) {
                        $item->variant->increment('stock_quantity', (int) $item->quantity);

                        InventoryTransaction::create([
                            'product_variant_id' => $item->product_variant_id,
                            'type' => 'return',
                            'quantity' => (int) $item->quantity,
                            'note' => 'Trả hàng về kho do giao thất bại: ' . $shipment->order->order_code,
                        ]);
                    }
                }

                $shipment->order->update(['status' => 'returned']);

                return;
            }

            $shipment->order->update(['status' => 'shipping']);
        });

        $this->notifyShipmentUpdate($shipment, $newStatus);
    }

    protected function notifyShipmentUpdate(Shipment $shipment, string $status): void
    {
        try {
            $order = $shipment->order;
            $to = $order->user->email ?? null;
            $subject = "Cập nhật vận đơn: {$order->order_code} - {$status}";
            $body = "Vận đơn {$shipment->tracking_code} đã được cập nhật trạng thái: {$status}.";

            if ($to) {
                Mail::raw($body, function ($m) use ($to, $subject) {
                    $m->to($to)->subject($subject);
                });
            }

            Log::info('Notify shipment update', ['order' => $order->id, 'status' => $status]);
        } catch (\Exception $e) {
            Log::error('Failed to send shipment notification: ' . $e->getMessage());
        }
    }
}

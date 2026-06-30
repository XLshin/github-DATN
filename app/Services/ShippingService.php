<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\Carrier;
use App\Models\ShipmentItem;
use App\Models\Imei;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    /**
     * Create shipment for an order using a selected carrier.
     * This is a simple implementation that acts as an adapter point for real carriers.
     */
    public function createShipment(Order $order, Carrier $carrier, array $options = []): Shipment
    {
        // create shipment record - insert only columns that exist to support different migrations
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

        // map items
        foreach ($order->items as $item) {
            ShipmentItem::create([
                'shipment_id' => $shipment->id,
                'order_item_id' => $item->id,
                'quantity' => $item->quantity,
            ]);
        }

        // Placeholder: call carrier API here to create label. We'll simulate success.
        try {
            // Simulate carrier response
            $shipment->shipment_code = 'CARRIER-' . strtoupper(uniqid());
            $shipment->tracking_url = 'https://tracking.example/' . $shipment->shipment_code;
            $shipment->status = 'label_created';
            $shipment->save();

            Log::info('Shipment label created', ['shipment_id' => $shipment->id]);

            // notify customer
            $this->notifyShipmentUpdate($shipment, 'label_created');
        } catch (\Exception $e) {
            Log::error('Failed to create carrier label: ' . $e->getMessage());
            $shipment->status = 'failed';
            $shipment->save();
        }

        return $shipment;
    }

    /**
     * Update shipment status with business logic (decrement stock, rollback on failure, notify)
     */
    public function updateShipmentStatus(\App\Models\Shipment $shipment, string $newStatus, array $meta = []): void
    {
        DB::transaction(function () use ($shipment, $newStatus, $meta) {
            $data = [];

            if ($newStatus === 'shipping') {
                if (Schema::hasColumn('shipments', 'shipped_at') && is_null($shipment->shipped_at)) {
                    $data['shipped_at'] = now();
                }

                // decrement stock for each order item
                foreach ($shipment->order->items as $item) {
                    $variant = $item->variant()->lockForUpdate()->first();
                    if ($variant) {
                        if ($variant->stock_quantity < $item->quantity) {
                            throw new \RuntimeException('Không đủ tồn kho để chuyển đơn hàng.');
                        }
                        $variant->stock_quantity -= $item->quantity;
                        $variant->save();
                    }
                }
            }

            if ($newStatus === 'delivered') {
                $data['delivered_at'] = now();
            }

            $data['shipping_status'] = $newStatus;

            $shipment->update($data + ['metadata' => array_merge($shipment->metadata ?? [], $meta)]);

            // update order status
            if ($newStatus === 'delivered') {
                $shipment->order->update(['status' => 'completed']);
            } elseif ($newStatus === 'failed') {
                // rollback IMEIs and stock
                foreach ($shipment->order->items as $item) {
                    if ($item->imei_id) {
                        $imei = Imei::lockForUpdate()->find($item->imei_id);
                        if ($imei) {
                            $imei->status = 'available';
                            $imei->reserved_by_order_item_id = null;
                            $imei->reserved_at = null;
                            $imei->save();
                        }

                        $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
                        if ($variant) {
                            $variant->stock_quantity += $item->quantity;
                            $variant->save();
                        }

                        $item->imei_id = null;
                        $item->save();
                    }
                }

                $shipment->order->update(['status' => 'returned']);
            } else {
                $shipment->order->update(['status' => 'shipping']);
            }
        });

        // send notifications
        $this->notifyShipmentUpdate($shipment, $newStatus);
    }

    protected function notifyShipmentUpdate(\App\Models\Shipment $shipment, string $status): void
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

            // admin notification via log as placeholder
            Log::info('Notify shipment update', ['order' => $order->id, 'status' => $status]);
        } catch (\Exception $e) {
            Log::error('Failed to send shipment notification: ' . $e->getMessage());
        }
    }
}

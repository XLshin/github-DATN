<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Imei;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImeiReservationService
{
    /**
     * Reserve IMEIs for an order's items. Returns true on success, false on failure.
     */
    public function reserve(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $qty = $item->quantity;

                $imeis = Imei::where('product_variant_id', $item->product_variant_id)
                    ->where('status', 'available')
                    ->lockForUpdate()
                    ->limit($qty)
                    ->get();

                if ($imeis->count() < $qty) {
                    // Not enough IMEIs, rollback
                    return false;
                }

                foreach ($imeis as $imei) {
                    $imei->status = 'reserved';
                    $imei->reserved_at = Carbon::now();
                    $imei->reserved_by_order_item_id = $item->id;
                    $imei->save();
                }
            }

            return true;
        });
    }

    /**
     * Finalize assignment: convert reserved imeis to sold and attach to order_items.
     */
    public function finalize(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $reservedImeis = Imei::where('reserved_by_order_item_id', $item->id)
                    ->where('status', 'reserved')
                    ->lockForUpdate()
                    ->get();

                foreach ($reservedImeis as $imei) {
                    $imei->status = 'sold';
                    $imei->reserved_by_order_item_id = null;
                    $imei->reserved_at = null;
                    $imei->save();

                    // attach to order item (single imei per order_item expected)
                    if (! $item->imei_id) {
                        $item->imei_id = $imei->id;
                        $item->save();
                    }
                }
            }
        });
    }

    /**
     * Release reserved IMEIs for an order (on payment failure or timeout).
     */
    public function release(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                Imei::where('reserved_by_order_item_id', $item->id)
                    ->where('status', 'reserved')
                    ->lockForUpdate()
                    ->get()
                    ->each(function (Imei $imei) {
                        $imei->status = 'available';
                        $imei->reserved_by_order_item_id = null;
                        $imei->reserved_at = null;
                        $imei->save();
                    });
            }
        });
    }
}

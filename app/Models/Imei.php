<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Imei extends Model
{
    protected $table = 'imeis';

    protected $fillable = [
        'product_variant_id',
        'imei',
        'status',
        'reserved_at',
        'reserved_by_order_item_id',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function warranty()
    {
        return $this->hasOne(Warranty::class);
    }

    public function reservedByOrderItem()
    {
        return $this->belongsTo(OrderItem::class, 'reserved_by_order_item_id');
    }

    public function orderItem()
    {
        return $this->hasOne(OrderItem::class, 'imei_id');
    }

    public function reserveForOrderItem(OrderItem $item): void
    {
        $this->update([
            'status' => 'reserved',
            'reserved_at' => now(),
            'reserved_by_order_item_id' => $item->getKey(),
        ]);

        $item->update([
            'imei_id' => $this->getKey(),
        ]);
    }

    public function releaseReservation(): void
    {
        $this->update([
            'status' => 'available',
            'reserved_at' => null,
            'reserved_by_order_item_id' => null,
        ]);
    }

    public function markAsSold(): void
    {
        $this->update([
            'status' => 'sold',
            'reserved_at' => null,
            'reserved_by_order_item_id' => null,
        ]);
    }
}
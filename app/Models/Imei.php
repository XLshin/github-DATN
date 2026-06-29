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
        'reserved_by_order_item_id'
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

    public function assignToOrderItem(OrderItem $item)
    {
        $this->status = 'sold';
        $this->reserved_by_order_item_id = null;
        $this->reserved_at = null;
        $this->save();

        $item->imei_id = $this->id;
        $item->save();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'carrier_id',
        'shipment_code',
        'status',
        'cost',
        'service_type',
        'tracking_url',
        'requested_at',
        'picked_up_at',
        'delivered_at',
        'metadata',
        'shipping_unit',
        'tracking_code',
        'shipping_status',
        'shipped_at',
        'delivered_at',
        'shipped_image',
        'delivered_image',
    ];

    protected $casts = [
        'metadata' => 'array',
        'requested_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }
}

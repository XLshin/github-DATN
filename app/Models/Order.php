<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [

    'user_id',

    'order_code',

    'customer_name',

    'customer_phone',

    'shipping_address',

    'subtotal',

    'membership_discount',

    'coupon_discount',

    'total_amount',

    'status'
];

public function user()
{
    return $this->belongsTo(User::class);
}

public function items()
{
    return $this->hasMany(OrderItem::class);
}

public function payments()
{
    return $this->hasMany(Payment::class);
}

public function shipments()
{
    return $this->hasMany(Shipment::class);
}

public function warranties()
{
    return $this->hasMany(Warranty::class);
}
}

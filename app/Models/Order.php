<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
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

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function warranties()
    {
        return $this->hasMany(Warranty::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Whether the order is editable by admin.
     * Orders in 'shipping' state should not be editable until delivery result.
     */
    public function isEditable(): bool
    {
        return $this->status !== 'shipping';
    }
}

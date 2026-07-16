<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Coupon;
use App\Models\OrderProof;
use App\Models\OrderReceiver;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'buyer_type',
        'buyer_name',
        'buyer_phone',
        'order_code',
        'customer_name',
        'customer_phone',
        'shipping_address',
        'buyer_type',
        'buyer_name',
        'buyer_phone',
        'subtotal',
        'membership_discount',
        'coupon_discount',
        'points_used',
        'points_discount',
        'coupon_id',
        'coupon_code',
        'total_amount',
        'status',

        'fulfillment_status',
        'confirmed_at',
        'packed_at',
        'handed_over_at',
        'delivered_at',
        'cancelled_at',
        'shipping_label_printed_at',
        'cancel_reason',
        'cancelled_by',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'packed_at' => 'datetime',
        'handed_over_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'shipping_label_printed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function receiver()
    {
        return $this->hasOne(OrderReceiver::class);
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

    public function refundRequest()
    {
        return $this->hasOne(RefundRequest::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function proofs()
    {
        return $this->hasMany(OrderProof::class);
    }

    public function packedProofs()
    {
        return $this->hasMany(OrderProof::class)->where('type', 'packed');
    }

    public function deliveredProofs()
    {
        return $this->hasMany(OrderProof::class)->where('type', 'delivered');
    }

    public function isEditable(): bool
    {
        return !in_array($this->fulfillment_status, [
            'shipping',
            'completed',
            'cancelled',
        ], true);
    }

    public function getFulfillmentStatusLabelAttribute(): string
    {
        return match ($this->fulfillment_status) {
            'pending' => 'Chờ xử lý',
            'waiting_pack' => 'Chờ đóng gói',
            'waiting_handover' => 'Chờ bàn giao',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'failed' => 'Giao thất bại',
            default => $this->fulfillment_status ?? 'Không xác định',
        };
    }
}
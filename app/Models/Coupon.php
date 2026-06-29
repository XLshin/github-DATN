<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    protected $fillable = [

        'code',

        'discount_type',

        'discount_value',

        'min_order_amount',

        'usage_limit',

        'used_count',

        'start_date',

        'end_date',

        'status'

    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => 'boolean',
    ];

    /**
     * Relationship: Users who can use this coupon
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coupon_user');
    }

    /**
     * Kiểm tra voucher còn hiệu lực không
     */
    public function isValid(): bool
    {
        $hasUsageLeft = $this->usage_limit === 0 || $this->used_count < $this->usage_limit;

        return $this->status
            && now()->between($this->start_date, $this->end_date)
            && $hasUsageLeft;
    }

    public function isValidForAmount(float $subtotal): bool
    {
        return $this->isValid() && $subtotal >= $this->min_order_amount;
    }

    public function discountAmount(float $subtotal): float
    {
        return $this->discount_type === 'percent'
            ? round($subtotal * ($this->discount_value / 100), 2)
            : min($this->discount_value, $subtotal);
    }
}

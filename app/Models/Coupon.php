<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
     * Kiểm tra voucher còn hiệu lực không
     */
    public function isValid()
    {
        return $this->status
            && now()->between($this->start_date, $this->end_date)
            && $this->used_count < $this->usage_limit;
    }
}

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
}

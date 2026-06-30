<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Imei extends Model
{
    protected $table = 'imeis';

    protected $fillable = [
        'product_variant_id',
        'imei',
        'status'
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function warranty()
    {
        return $this->hasOne(Warranty::class);
    }
        public function order(): HasOneThrough
    {
        return $this->hasOneThrough(
            Order::class,
            Warranty::class,
            'imei_id',
            'id',
            'id',
            'order_id'
        );
    }
}

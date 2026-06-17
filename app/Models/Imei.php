<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Imei extends Model
{
    protected $fillable = [
        'product_variant_id',
        'imei',
        'status',
    ];

    public function warranties()
    {
        return $this->hasMany(Warranty::class);
    }

    public function currentWarranty()
    {
        return $this->hasOne(Warranty::class)
            ->whereIn('status', ['active', 'claimed'])
            ->latestOfMany();
    }
}
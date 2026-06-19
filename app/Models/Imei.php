<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IMEI extends Model
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

    public function warranties()
    {
        return $this->hasMany(Warranty::class);
    }
}

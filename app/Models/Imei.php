<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Imei extends Model
{
    protected $fillable = [
        'product_variant_id',
        'imei',
        'status'
    ];
}

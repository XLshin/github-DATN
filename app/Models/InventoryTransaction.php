<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'product_variant_id',
        'type',
        'quantity',
        'note'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'product_variant_id',
        'user_id',
        'type',
        'quantity',
        'note',
    ];

    protected static function booted(): void
    {
        static::creating(function (InventoryTransaction $transaction) {
            if (!$transaction->user_id && auth()->check()) {
                $transaction->user_id = auth()->id();
            }
        });
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

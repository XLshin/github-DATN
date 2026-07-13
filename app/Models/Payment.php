<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'payment_status',
        'transaction_code',
        'payer_name',
        'payer_note',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'paid_at'    => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->payment_status === 'pending'
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

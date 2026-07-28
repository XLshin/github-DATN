<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** Số lần thử thanh toán tối đa (kể cả lần đầu) trước khi đơn tự động bị hủy. */
    public const MAX_ATTEMPTS = 2;

    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'payment_status',
        'attempt_count',
        'transaction_code',
        'payer_name',
        'payer_note',
        'proof_image',
        'confirmed_by',
        'rejected_by',
        'reject_reason',
        'admin_note',
        'paid_at',
        'expires_at',
        'simulate_confirm_at',
    ];

    protected $casts = [
        'paid_at'             => 'datetime',
        'expires_at'          => 'datetime',
        'simulate_confirm_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->payment_status === 'pending'
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function hasRetriesLeft(): bool
    {
        return $this->attempt_count < self::MAX_ATTEMPTS;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}

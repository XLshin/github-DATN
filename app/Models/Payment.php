<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
<<<<<<< HEAD
    /** Số lần thử thanh toán tối đa (kể cả lần đầu) trước khi đơn tự động bị hủy. */
    public const MAX_ATTEMPTS = 2;

=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'payment_status',
<<<<<<< HEAD
        'attempt_count',
=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
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
<<<<<<< HEAD
        'simulate_confirm_at',
    ];

    protected $casts = [
        'paid_at'             => 'datetime',
        'expires_at'          => 'datetime',
        'simulate_confirm_at' => 'datetime',
=======
    ];

    protected $casts = [
        'paid_at'    => 'datetime',
        'expires_at' => 'datetime',
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
    ];

    public function isExpired(): bool
    {
        return $this->payment_status === 'pending'
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

<<<<<<< HEAD
    public function hasRetriesLeft(): bool
    {
        return $this->attempt_count < self::MAX_ATTEMPTS;
    }

=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
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

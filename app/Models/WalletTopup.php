<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTopup extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'payment_method',
        'payment_status',
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
=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
<<<<<<< HEAD
        'simulate_confirm_at' => 'datetime',
=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
    ];

    /** Mã tham chiếu duy nhất khách phải ghi đúng khi chuyển khoản, dùng để đối soát. */
    public function referenceCode(): string
    {
        return 'NAPVI' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function isExpired(): bool
    {
        return $this->payment_status === 'pending'
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }
}

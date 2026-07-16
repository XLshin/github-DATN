<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundRequest extends Model
{
    /** Số ngày xử lý tối thiểu bắt buộc đối với hoàn tiền qua ngân hàng. */
    public const MIN_BANK_PROCESSING_DAYS = 7;

    protected $fillable = [
        'order_id',
        'user_id',
        'method',
        'amount',
        'status',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'requested_at',
        'eligible_at',
        'completed_at',
        'admin_note',
        'proof_image',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'eligible_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isEligibleToComplete(): bool
    {
        if ($this->method === 'wallet') {
            return true;
        }

        return $this->eligible_at !== null && ! $this->eligible_at->isFuture();
    }
}

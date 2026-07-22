<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletWithdrawal extends Model
{
    /** Số ngày làm việc xử lý tối thiểu bắt buộc trước khi admin được phép hoàn tất rút tiền. */
    public const MIN_PROCESSING_DAYS = 1;

    /** Số tiền rút tối thiểu mỗi lần. */
    public const MIN_AMOUNT = 50000;

    /** Rút dưới ngưỡng này được tự động xử lý (mô phỏng), không cần admin duyệt thủ công. */
    public const AUTO_WITHDRAWAL_MAX_AMOUNT = 2000000;

    protected $fillable = [
        'user_id',
        'bank_account_id',
        'bank_name',
        'account_number',
        'account_holder_name',
        'amount',
        'status',
        'requested_at',
        'eligible_at',
        'simulate_confirm_at',
        'completed_at',
        'transaction_code',
        'confirmed_by',
        'rejected_by',
        'reject_reason',
        'admin_note',
        'proof_image',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'eligible_at' => 'datetime',
        'simulate_confirm_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function isEligibleToComplete(): bool
    {
        return $this->eligible_at !== null && ! $this->eligible_at->isFuture();
    }
}

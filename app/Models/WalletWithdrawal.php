<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletWithdrawal extends Model
{
    /** Số ngày làm việc xử lý tối thiểu bắt buộc trước khi admin được phép hoàn tất rút tiền. */
    public const MIN_PROCESSING_DAYS = 1;

    /** Số tiền rút tối thiểu mỗi lần — không giới hạn (chỉ cần > 0). */
    public const MIN_AMOUNT = 1;

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

    /** Mã ngân hàng chuẩn VietQR/Napas ứng với tên ngân hàng hiển thị (dùng để tạo QR chuyển tiền ra). */
    public const VIETQR_BANK_CODES = [
        'Vietcombank' => 'VCB',
        'VietinBank' => 'ICB',
        'BIDV' => 'BIDV',
        'Agribank' => 'VBA',
        'Techcombank' => 'TCB',
        'MB Bank' => 'MB',
        'ACB' => 'ACB',
        'VPBank' => 'VPB',
        'Sacombank' => 'STB',
        'TPBank' => 'TPB',
        'HDBank' => 'HDB',
        'SHB' => 'SHB',
        'VIB' => 'VIB',
        'Eximbank' => 'EIB',
        'MSB' => 'MSB',
        'OCB' => 'OCB',
        'SeABank' => 'SEAB',
        'SCB' => 'SCB',
        'Nam A Bank' => 'NAB',
        'BacA Bank' => 'BAB',
        'PVcomBank' => 'PVCB',
        'Vietbank' => 'VBB',
        'ABBANK' => 'ABB',
        'LPBank' => 'LPB',
        'Kienlongbank' => 'KLB',
    ];

    /** @return string|null null nếu không nhận diện được ngân hàng (không tạo được QR, chỉ chuyển thủ công). */
    public function vietQrBankCode(): ?string
    {
        return self::VIETQR_BANK_CODES[$this->bank_name] ?? null;
    }

    /**
     * URL ảnh QR VietQR điền sẵn tài khoản khách + số tiền + nội dung — admin quét bằng app ngân
     * hàng để chuyển tiền ra thật chỉ trong 1 lần quét, không cần gõ tay số tài khoản/số tiền.
     */
    public function vietQrPayoutUrl(): ?string
    {
        $bankCode = $this->vietQrBankCode();

        if (! $bankCode) {
            return null;
        }

        $amount = (int) round((float) $this->amount);
        $info = urlencode((string) $this->transaction_code);
        $name = urlencode((string) $this->account_holder_name);

        return "https://img.vietqr.io/image/{$bankCode}-{$this->account_number}-compact.jpg?amount={$amount}&addInfo={$info}&accountName={$name}";
    }
}

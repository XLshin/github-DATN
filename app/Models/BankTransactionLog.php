<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransactionLog extends Model
{
    protected $fillable = [
        'type',
        'direction',
        'user_id',
        'reference_type',
        'reference_id',
        'amount',
        'payment_method',
        'bank_name',
        'account_number',
        'account_holder_name',
        'status',
        'transaction_code',
        'proof_image',
        'handled_by',
        'note',
        'occurred_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function reference(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'topup' => 'Nạp ví',
            'withdrawal' => 'Rút tiền',
            'refund' => 'Hoàn tiền',
            'order_payment' => 'Thanh toán đơn hàng',
            default => $this->type,
        };
    }

    public function methodLabel(): string
    {
        return match ($this->payment_method) {
            'bank_transfer' => 'Chuyển khoản',
            'momo' => 'MoMo',
            'vnpay' => 'VNPAY',
            'card' => 'Thẻ',
            'wallet' => 'Ví ByteZone',
            default => $this->payment_method ?? '-',
        };
    }
}

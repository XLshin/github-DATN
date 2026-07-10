<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CLAIMED = 'claimed';

    protected $fillable = [
        'imei_id',
        'order_id',
        'warranty_start',
        'warranty_end',
        'status',
        'customer_note',
        'status_update_note',
        'repair_result_note',
        'customer_receipt_note',
        'completed_at',
    ];

    protected $casts = [
        'warranty_start' => 'date',
        'warranty_end' => 'date',
        'completed_at' => 'datetime',
    ];

    public function imei()
    {
        return $this->belongsTo(Imei::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function media()
    {
        return $this->hasMany(WarrantyMedia::class);
    }

    public function receptionMedia()
    {
        return $this->hasMany(WarrantyMedia::class)
            ->where('stage', WarrantyMedia::STAGE_RECEPTION)
            ->orderBy('id');
    }

    public function completionMedia()
    {
        return $this->hasMany(WarrantyMedia::class)
            ->where('stage', WarrantyMedia::STAGE_COMPLETION)
            ->orderBy('id');
    }

    public function receiptMedia()
    {
        return $this->hasMany(WarrantyMedia::class)
            ->where('stage', WarrantyMedia::STAGE_CUSTOMER_RECEIPT)
            ->orderBy('id');
    }

    public function getWarrantyCodeAttribute(): string
    {
        return 'BH' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CLAIMED => 'Đang xử lý bảo hành',

            self::STATUS_ACTIVE,
            self::STATUS_EXPIRED => 'Hoàn tất xử lý',

            default => (string) $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CLAIMED => 'warning',

            self::STATUS_ACTIVE,
            self::STATUS_EXPIRED => 'success',

            default => 'light',
        };
    }
}
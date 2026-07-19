<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CLAIMED = 'claimed';

    public const FAULT_STORE = 'store';
    public const FAULT_MANUFACTURER = 'manufacturer';
    public const FAULT_CUSTOMER = 'customer';
    public const FAULT_UNKNOWN = 'unknown';

    public const RESOLUTION_REPAIR = 'repair';
    public const RESOLUTION_REPLACE = 'replace';
    public const RESOLUTION_REJECT = 'reject';

    public const REPLACEMENT_PERIOD_DAYS = 30;

    protected $fillable = [
        'imei_id',
        'order_id',
        'warranty_start',
        'warranty_end',
        'status',
        'customer_note',
        'fault_source',
        'resolution_type',
        'replacement_imei_id',
        'replaced_at',
        'status_update_note',
        'repair_result_note',
        'customer_receipt_note',
        'completed_at',
    ];

    protected $casts = [
        'warranty_start' => 'date',
        'warranty_end' => 'date',
        'completed_at' => 'datetime',
        'replaced_at' => 'datetime',
    ];

    public function imei()
    {
        return $this->belongsTo(Imei::class);
    }

    public function replacementImei()
    {
        return $this->belongsTo(Imei::class, 'replacement_imei_id');
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

    public function getFaultSourceLabelAttribute(): string
    {
        return match ($this->fault_source) {
            self::FAULT_STORE => 'Lỗi do cửa hàng',
            self::FAULT_MANUFACTURER => 'Lỗi do hãng',
            self::FAULT_CUSTOMER => 'Lỗi do khách hàng',
            self::FAULT_UNKNOWN => 'Chưa xác định',
            default => 'Chưa cập nhật',
        };
    }

    public function getResolutionTypeLabelAttribute(): string
    {
        return match ($this->resolution_type) {
            self::RESOLUTION_REPAIR => 'Sửa chữa bảo hành',
            self::RESOLUTION_REPLACE => 'Đổi máy mới',
            self::RESOLUTION_REJECT => 'Từ chối bảo hành',
            default => 'Chưa cập nhật',
        };
    }
}

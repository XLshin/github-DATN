<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CLAIMED = 'claimed';

<<<<<<< HEAD
=======
<<<<<<< HEAD
=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
    public const FAULT_STORE = 'store';
    public const FAULT_MANUFACTURER = 'manufacturer';
    public const FAULT_CUSTOMER = 'customer';
    public const FAULT_UNKNOWN = 'unknown';

    public const RESOLUTION_REPAIR = 'repair';
    public const RESOLUTION_REPLACE = 'replace';
    public const RESOLUTION_REJECT = 'reject';

    public const REPLACEMENT_PERIOD_DAYS = 30;

<<<<<<< HEAD
=======
>>>>>>> e3a755cc0ad1d671c82fe41fc2212481154a14db
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
    protected $fillable = [
        'imei_id',
        'order_id',
        'warranty_start',
        'warranty_end',
        'status',
        'customer_note',
<<<<<<< HEAD
=======
<<<<<<< HEAD
=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
        'fault_source',
        'resolution_type',
        'replacement_imei_id',
        'replaced_at',
<<<<<<< HEAD
=======
>>>>>>> e3a755cc0ad1d671c82fe41fc2212481154a14db
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
        'status_update_note',
        'repair_result_note',
        'customer_receipt_note',
        'completed_at',
    ];

    protected $casts = [
        'warranty_start' => 'date',
        'warranty_end' => 'date',
        'completed_at' => 'datetime',
<<<<<<< HEAD
        'replaced_at' => 'datetime',
=======
<<<<<<< HEAD
=======
        'replaced_at' => 'datetime',
>>>>>>> e3a755cc0ad1d671c82fe41fc2212481154a14db
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
    ];

    public function imei()
    {
        return $this->belongsTo(Imei::class);
<<<<<<< HEAD
=======
    }

    public function replacementImei()
    {
        return $this->belongsTo(Imei::class, 'replacement_imei_id');
>>>>>>> e3a755cc0ad1d671c82fe41fc2212481154a14db
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
<<<<<<< HEAD
            self::STATUS_ACTIVE,
            self::STATUS_EXPIRED => 'Hoàn tất xử lý',
=======
<<<<<<< HEAD

            self::STATUS_ACTIVE,
            self::STATUS_EXPIRED => 'Hoàn tất xử lý',

=======
            self::STATUS_ACTIVE,
            self::STATUS_EXPIRED => 'Hoàn tất xử lý',
>>>>>>> e3a755cc0ad1d671c82fe41fc2212481154a14db
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
            default => (string) $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CLAIMED => 'warning',
<<<<<<< HEAD
=======
<<<<<<< HEAD

            self::STATUS_ACTIVE,
            self::STATUS_EXPIRED => 'success',

            default => 'light',
        };
    }
}
=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
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
<<<<<<< HEAD
=======
>>>>>>> e3a755cc0ad1d671c82fe41fc2212481154a14db
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WarrantyMedia extends Model
{
    protected $table = 'warranty_media';

    public const STAGE_RECEPTION = 'reception';
    public const STAGE_COMPLETION = 'completion';

    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';

    public const STAGE_CUSTOMER_RECEIPT = 'customer_receipt';
    protected $fillable = [
        'warranty_id',
        'stage',
        'type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function warranty()
    {
        return $this->belongsTo(Warranty::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_IMAGE => 'Ảnh',
            self::TYPE_VIDEO => 'Video',
            default => (string) $this->type,
        };
    }

    public function getStageLabelAttribute(): string
    {
        return match ($this->stage) {
            self::STAGE_RECEPTION => 'Tiếp nhận bảo hành',
            self::STAGE_COMPLETION => 'Sau khi sửa xong',
            self::STAGE_CUSTOMER_RECEIPT => 'Khách nhận lại máy',
            default => (string) $this->stage,
        };
    }
}
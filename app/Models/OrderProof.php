<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProof extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'image_path',
        'note',
        'created_by',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'packed' => 'Ảnh đóng gói',
            'delivered' => 'Ảnh giao hàng thành công',
            'failed_delivery' => 'Ảnh giao hàng thất bại',
            default => $this->type,
        };
    }
}
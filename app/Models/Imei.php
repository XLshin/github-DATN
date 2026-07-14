<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Imei extends Model
{
    protected $table = 'imeis';

    protected $fillable = [
        'product_variant_id',
        'imei',
        'status',
        'reserved_at',
        'reserved_by_order_item_id',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function warranty()
    {
        return $this->hasOne(Warranty::class);
    }

    /**
     * Order item mà IMEI này đang/đã được gán vào.
     * Đây là liên kết duy nhất (nguồn sự thật) giữa 1 IMEI vật lý và 1 dòng order item,
     * được giữ lại cả sau khi bán (sold) để tra cứu lịch sử/bảo hành.
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'reserved_by_order_item_id');
    }

    // Giữ alias để tương thích ngược với những chỗ code cũ gọi reservedByOrderItem().
    public function reservedByOrderItem()
    {
        return $this->orderItem();
    }

    public function reserveForOrderItem(OrderItem $item): void
    {
        $this->update([
            'status' => 'reserved',
            'reserved_at' => now(),
            'reserved_by_order_item_id' => $item->getKey(),
        ]);
    }

    public function releaseReservation(): void
    {
        $this->update([
            'status' => 'available',
            'reserved_at' => null,
            'reserved_by_order_item_id' => null,
        ]);
    }

    public function markAsSold(): void
    {
        // Không xoá reserved_by_order_item_id nữa: đây là liên kết vĩnh viễn
        // để còn tra cứu đơn hàng/khách hàng khi làm phiếu bảo hành sau này.
        $this->update([
            'status' => 'sold',
            'reserved_at' => null,
        ]);
    }

    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class, 'imei_id')
            ->orderByDesc('created_at');
    }
}
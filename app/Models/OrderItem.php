<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'price',
        'quantity',
        'total',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Một order item (quantity >= 1) có thể được gán nhiều IMEI,
     * mỗi IMEI vật lý tương ứng với 1 đơn vị sản phẩm trong dòng đơn hàng này.
     */
    public function imeis()
    {
        return $this->hasMany(Imei::class, 'reserved_by_order_item_id');
    }

    public function needsImei(): bool
    {
        return ($this->product->product_type ?? null) === 'imei/serial';
    }

    /**
     * Số lượng IMEI còn thiếu so với số lượng đặt (quantity).
     */
    public function remainingImeiSlots(): int
    {
        if (!$this->needsImei()) {
            return 0;
        }

        return max(0, (int) $this->quantity - $this->imeis()->count());
    }

    /**
     * Đã gán đủ IMEI cho toàn bộ số lượng của dòng đơn hàng này chưa.
     */
    public function hasFullImeiAssignment(): bool
    {
        if (!$this->needsImei()) {
            return true;
        }

        return $this->remainingImeiSlots() === 0;
    }
}
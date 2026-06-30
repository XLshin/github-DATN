<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [

        'product_id',

        'color',

        'storage',

        'image_path',

        'stock_quantity',

        'additional_price',

        'status'

    ];

    public function product()
{
    return $this->belongsTo(Product::class);
}

public function cartItems()
{
    return $this->hasMany(CartItem::class);
}

public function orderItems()
{
    return $this->hasMany(OrderItem::class);
}

public function imeis()
{
    return $this->hasMany(Imei::class);
}

public function inventoryTransactions()
{
    return $this->hasMany(InventoryTransaction::class);
}

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_variant_id');
    }
}

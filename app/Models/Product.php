<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [

        'category_id',

        'brand_id',

        'name',

        'slug',

        'description',

        'price',

        'stock_quantity',

        'thumbnail',

        'status'

    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    
    public function cartItems()
{
    return $this->hasMany(CartItem::class);
}

public function orderItems()
{
    return $this->hasMany(OrderItem::class);
}

public function variants()
{
    return $this->hasMany(ProductVariant::class);
}
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductGroup extends Model
{
    use HasFactory;

    protected $casts = [
        'product_type' => 'string',
    ];

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'description',
        'status',
        'product_type',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function specifications()
    {
        return $this->hasMany(ProductSpecification::class)->orderBy('sort_order')->orderBy('id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function getProductTypeAttribute($value): string
    {
        $normalized = is_string($value) ? trim($value) : '';

        return in_array($normalized, ['imei/serial', 'quantity'], true) ? $normalized : 'quantity';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSpecification extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_group_id',
        'group_name',
        'name',
        'value',
        'sort_order',
    ];

    public function productGroup()
    {
        return $this->belongsTo(ProductGroup::class);
    }
}

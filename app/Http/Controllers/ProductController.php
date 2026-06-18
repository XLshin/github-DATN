<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        $product->load([
    'reviews' => function ($query) {
        $query->where('status', true);
    },
    'reviews.user'
]);
        return view(
            'products.show',
            compact('product')
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        $product->load([
            'reviews' => fn ($query) => $query->where('status', true),
            'reviews.user',
        ]);

        return view('client.products.show', compact('product'));
    }
}

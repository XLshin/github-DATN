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
            'variants'  => fn ($q) => $q->where('status', 1)->orderBy('id'),
        ]);

        // Tính tồn kho theo loại sản phẩm
        $product->variants->each(function ($variant) use ($product) {
            if ($product->product_type === 'imei/serial') {
                $variant->available_stock = $variant->imeis()->where('status', 'available')->count();
            } else {
                $variant->available_stock = max(0, (int) $variant->stock_quantity);
            }
        });

        return view('client.products.show', compact('product'));
    }
}

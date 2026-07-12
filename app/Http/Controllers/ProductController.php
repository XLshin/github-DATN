<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        $product->load([
            'category',
            'brand',
            'images',
            'variants' => fn ($query) => $query
                ->where('status', true)
                ->with('images')
                ->withCount([
                    'imeis as available_imeis_count' => fn ($imeiQuery) => $imeiQuery->where('status', 'available'),
                ])
                ->orderBy('id'),
            'productGroup.category',
            'productGroup.brand',
            'productGroup.images',
            'productGroup.specifications',
            'productGroup.products' => fn ($query) => $query
                ->where('status', true)
                ->orderByRaw('price IS NULL')
                ->orderBy('price')
                ->orderBy('id'),
            'reviews' => fn ($query) => $query->where('status', true),
            'reviews.user',
        ]);

        $relatedProducts = Product::query()
            ->with([
                'brand',
                'images',
                'productGroup.images',
                'variants.images',
            ])
            ->where('status', true)
            ->where('brand_id', $product->brand_id)
            ->whereKeyNot($product->id)
            ->latest()
            ->limit(4)
            ->get();

        return view('client.products.show', compact('product', 'relatedProducts'));
    }
}

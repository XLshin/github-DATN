<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
<<<<<<< HEAD
        $products = Product::with(['category', 'images'])
            ->latest()
            ->paginate(12);
=======
        $banners = Banner::active()->get();
>>>>>>> origin/main

        $categories = Category::withCount(['products' => fn($q) => $q->where('status', true)])
            ->having('products_count', '>', 0)
            ->get();

        $brands = Brand::withCount(['products' => fn($q) => $q->where('status', true)])
            ->having('products_count', '>', 0)
            ->get();

        // Sản phẩm mới nhất
        $newProducts = Product::with(['images', 'brand', 'category', 'variants'])
            ->where('status', true)
            ->latest()
            ->take(8)
            ->get();

        // Sản phẩm bán chạy (dựa trên tổng số lượng đã bán)
        $bestSellers = Product::with(['images', 'brand', 'category', 'variants'])
            ->where('status', true)
            ->withSum([
                'orderItems as sold_qty' => fn($q) => $q->whereHas(
                    'order', fn($o) => $o->whereIn('status', ['completed', 'shipping', 'processing'])
                )
            ], 'quantity')
            ->orderByDesc('sold_qty')
            ->take(8)
            ->get();

        return view('client.home', compact(
            'banners', 'categories', 'brands', 'newProducts', 'bestSellers'
        ));
    }

    public function byCategory(Category $category)
    {
        $products = Product::with(['images', 'brand', 'variants'])
            ->where('status', true)
            ->where('category_id', $category->id)
            ->when(request('brand_id'), fn($q) => $q->where('brand_id', request('brand_id')))
            ->paginate(12)
            ->withQueryString();

        $brands = Brand::whereHas('products', fn($q) => $q->where('status', true)->where('category_id', $category->id))
            ->get();

        return view('client.products.by_category', compact('category', 'products', 'brands'));
    }

    public function byBrand(Brand $brand)
    {
        $products = Product::with(['images', 'category', 'variants'])
            ->where('status', true)
            ->where('brand_id', $brand->id)
            ->when(request('category_id'), fn($q) => $q->where('category_id', request('category_id')))
            ->paginate(12)
            ->withQueryString();

        $categories = Category::whereHas('products', fn($q) => $q->where('status', true)->where('brand_id', $brand->id))
            ->get();

        return view('client.products.by_brand', compact('brand', 'products', 'categories'));
    }
}

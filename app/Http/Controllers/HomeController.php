<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $banners = Banner::where('status', true)->orderBy('id')->get();

        $categories = Category::whereHas('products', fn($q) => $q->where('status', true))
            ->withCount(['products' => fn($q) => $q->where('status', true)])
            ->get();

        $brands = Brand::whereHas('products', fn($q) => $q->where('status', true))
            ->withCount(['products' => fn($q) => $q->where('status', true)])
            ->get();

        // Sản phẩm mới nhất
        $newProducts = Product::with([
            'images',
            'brand',
            'category',
            'productGroup.images',
            'variants.images',
        ])
            ->where('status', true)
            ->latest()
            ->take(8)
            ->get();

        // Sản phẩm bán chạy (dựa trên tổng số lượng đã bán)
        $bestSellers = Product::with([
            'images',
            'brand',
            'category',
            'productGroup.images',
            'variants.images',
        ])
            ->where('status', true)
            ->withSum(['orderItems as sold_qty' => fn($q) => $q->whereHas(
                'order', fn($o) => $o->whereIn('status', ['completed', 'shipping', 'processing'])
            )], 'quantity')
            ->orderByDesc('sold_qty')->take(8)->get();

        // Flash sale: dùng tạm sản phẩm mới nhất
        $flashSaleProducts = Product::with(['images', 'brand', 'category', 'variants'])
            ->where('status', true)->latest()->take(8)->get();

        // Sản phẩm theo từng danh mục
        $productsByCategory = [];
        foreach (Category::all() as $cat) {
            $catProds = Product::with(['images', 'brand', 'category', 'variants'])
                ->where('status', true)
                ->where('category_id', $cat->id)
                ->latest()->take(8)->get();
            if ($catProds->isNotEmpty()) {
                $productsByCategory[$cat->name] = $catProds;
            }
        }

        // Phần filter sản phẩm
        $query = Product::with(['category', 'brand', 'variants', 'images'])
            ->where('status', true);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('color')) {
            $query->whereHas('variants', fn($q) => $q->where('color', $request->color));
        }
        if ($request->filled('storage')) {
            $query->where('storage', $request->storage);
        }
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }
        if ($request->filled('in_stock')) {
            $query->whereHas('variants', fn($q) => $q->where('stock_quantity', '>', 0));
        }

        match ($request->get('sort', 'latest')) {
            'price_asc'   => $query->orderBy('price', 'asc'),
            'price_desc'  => $query->orderBy('price', 'desc'),
            'best_seller' => $query->withCount(['orderItems as sold_count'])->orderBy('sold_count', 'desc'),
            default       => $query->latest(),
        };

        $allProducts  = $query->paginate(12)->withQueryString();
        $allCategories = Category::orderBy('name')->get();
        $allBrands     = Brand::orderBy('name')->get();
        $colors        = ProductVariant::distinct()->pluck('color')->filter()->sort()->values();
        $storages      = Product::distinct()->pluck('storage')->filter()->sort()->values();

        return view('client.home', compact(
            'banners', 'categories', 'brands', 'newProducts', 'bestSellers',
            'flashSaleProducts', 'productsByCategory',
            'allProducts', 'allCategories', 'allBrands', 'colors', 'storages'
        ));
    }

    public function byCategory($id)
    {
        $category = Category::findOrFail($id);

        $products = Product::with([
            'images',
            'brand',
            'productGroup.images',
            'variants.images',
        ])
            ->where('status', true)
            ->where('category_id', $category->id)
            ->when(request('brand_id'), fn($q) => $q->where('brand_id', request('brand_id')))
            ->paginate(12)->withQueryString();

        $brands = Brand::whereHas('products', fn($q) => $q->where('status', true)->where('category_id', $category->id))->get();

        return view('client.products.by_category', compact('category', 'products', 'brands'));
    }

    public function byBrand($id)
    {
        $brand = Brand::findOrFail($id);

        $products = Product::with([
            'images',
            'category',
            'productGroup.images',
            'variants.images',
        ])
            ->where('status', true)
            ->where('brand_id', $brand->id)
            ->when(request('category_id'), fn($q) => $q->where('category_id', request('category_id')))
            ->paginate(12)->withQueryString();

        $categories = Category::whereHas('products', fn($q) => $q
            ->where('status', true)
            ->where('brand_id', $brand->id)
        )->get();

        return view('client.products.by_brand', compact('brand', 'products', 'categories'));
    }
}

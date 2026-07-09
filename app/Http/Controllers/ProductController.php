<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
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
            $query->whereHas('variants', fn($q) => $q->where('storage', $request->storage));
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

        $products   = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();
        $colors     = ProductVariant::distinct()->pluck('color')->filter()->sort()->values();
        $storages   = ProductVariant::distinct()->pluck('storage')->filter()->sort()->values();

        return view('client.products.index', compact('products', 'categories', 'brands', 'colors', 'storages'));
    }

    public function show(Product $product)
    {
        $product->load([
            'brand',
            'category',
            'images',
            'variants',
            'reviews' => fn($query) => $query->where('status', true)->with('user'),
        ]);

        return view('client.products.show', compact('product'));
    }
}

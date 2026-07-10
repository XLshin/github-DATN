<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $products   = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();
        $colors     = ProductVariant::distinct()->pluck('color')->filter()->sort()->values();
        $storages   = Product::distinct()->pluck('storage')->filter()->sort()->values();

        return view('client.products.index', compact('products', 'categories', 'brands', 'colors', 'storages'));
    }

    public function show(Product $product)
    {
        $product->load([
            'brand',
            'category',
            'images',
            'variants.images',
            'reviews' => fn($query) => $query->where('status', true)->with('user'),
            'productGroup.specifications',
        ]);

        $productImages = collect([$product->thumbnail, ...$product->images->pluck('image_path')])
            ->filter()
            ->unique()
            ->map(fn ($path) => Storage::url($path))
            ->values();

        $variantData = $product->variants->map(function (ProductVariant $variant) use ($product, $productImages) {
            $variantImages = collect([$variant->image_path, ...$variant->images->pluck('image_path')])
                ->filter()
                ->unique()
                ->map(fn ($path) => Storage::url($path))
                ->values();

            // Tồn kho theo loại sản phẩm: imei/serial đếm theo IMEI khả dụng, còn lại theo cột stock_quantity
            $availableStock = $product->product_type === 'imei/serial'
                ? $variant->imeis()->where('status', 'available')->count()
                : max(0, (int) $variant->stock_quantity);

            return [
                'id' => $variant->id,
                'color' => $variant->color,
                'storage' => $product->storage,
                'stock_quantity' => $availableStock,
                'additional_price' => (float) $variant->additional_price,
                'price' => (float) $product->price + (float) $variant->additional_price,
                'image_url' => $variantImages->first() ?: $productImages->first(),
                'images' => $variantImages->values(),
                'is_available' => $availableStock > 0,
            ];
        })->values();

        $versionOptions = $variantData
            ->pluck('storage')
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->map(fn ($value) => trim((string) $value))
            ->unique()
            ->sort()
            ->values();

        $defaultStorage = $product->storage ?: ($versionOptions->first() ?? '');
        $defaultVariant = $variantData->firstWhere('storage', $defaultStorage) ?? $variantData->first();

        $initialColorOptions = collect();
        if ($defaultVariant) {
            $initialColorOptions = $variantData
                ->filter(fn ($variant) => (string) ($variant['storage'] ?? '') === (string) $defaultStorage)
                ->groupBy('color')
                ->filter(fn ($items, $color) => trim((string) $color) !== '')
                ->map(function ($items, $color) {
                    $variant = $items->first();

                    return [
                        'name' => $color,
                        'id' => $variant['id'],
                        'price' => $variant['price'],
                        'additional_price' => $variant['additional_price'],
                        'stock_quantity' => $variant['stock_quantity'],
                        'is_available' => $variant['is_available'],
                        'image_url' => $variant['image_url'],
                    ];
                })
                ->sortBy('name')
                ->values();
        }

        $specifications = $product->productGroup?->specifications ?? collect();

        return view('client.products.show', compact(
            'product',
            'productImages',
            'variantData',
            'versionOptions',
            'defaultStorage',
            'defaultVariant',
            'initialColorOptions',
            'specifications'
        ));
    }
}

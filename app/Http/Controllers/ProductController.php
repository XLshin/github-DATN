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
        $query = Product::with([
            'category',
            'brand',
            'variants.images',
            'images',
            'productGroup.images',
        ])
            ->where('status', true);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $numericSearch = preg_replace('/\D+/', '', $search);

            $query->where(function ($searchQuery) use ($search, $numericSearch) {
                $searchQuery
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('storage', 'like', '%' . $search . '%')
                    ->orWhereHas('productGroup', fn ($groupQuery) => $groupQuery->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('brand', fn ($brandQuery) => $brandQuery->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('variants', fn ($variantQuery) => $variantQuery->where('color', 'like', '%' . $search . '%'));

                if ($numericSearch !== '') {
                    $searchQuery->orWhere('price', 'like', '%' . $numericSearch . '%');
                }
            });
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
        $storages   = Product::distinct()->whereNotNull('storage')->pluck('storage')->filter()->sort()->values();

        return view('client.products.index', compact('products', 'categories', 'brands', 'colors', 'storages'));
    }

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
            'reviews' => fn ($query) => $query->where('status', true)->with('user'),
        ]);

        $productImages = collect([$product->thumbnail, ...$product->images->pluck('image_path')])
            ->filter()
            ->unique()
            ->map(fn ($path) => Storage::url($path))
            ->values();

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

    private function preferredAvailableProduct(Product $product): ?Product
    {
        if (! $product->product_group_id || $this->hasAvailableStock($product)) {
            return null;
        }

        return Product::query()
            ->where('product_group_id', $product->product_group_id)
            ->where('status', true)
            ->whereKeyNot($product->id)
            ->where(function ($query) {
                $query
                    ->where(function ($quantityQuery) {
                        $quantityQuery
                            ->where('product_type', 'quantity')
                            ->whereHas('variants', fn ($variantQuery) => $variantQuery->where('stock_quantity', '>', 0));
                    })
                    ->orWhere(function ($imeiQuery) {
                        $imeiQuery
                            ->where('product_type', 'imei/serial')
                            ->whereHas('variants.imeis', fn ($imeiStockQuery) => $imeiStockQuery->where('status', 'available'));
                    });
            })
            ->orderBy('price')
            ->orderBy('id')
            ->first();
    }

    private function hasAvailableStock(Product $product): bool
    {
        if ($product->product_type === 'imei/serial') {
            return $product->variants()
                ->whereHas('imeis', fn ($query) => $query->where('status', 'available'))
                ->exists();
        }

        return $product->variants()
            ->where('stock_quantity', '>', 0)
            ->exists();
    }
} 

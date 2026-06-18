<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'brand', 'variants'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->brand_id, fn($q) => $q->where('brand_id', $request->brand_id))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'category_id'    => 'required|exists:categories,id',
            'brand_id'       => 'required|exists:brands,id',
            'description'    => 'required|string',
            'price'          => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'thumbnail'      => 'nullable|image|max:2048',
            'status'         => 'boolean',
            'variants'       => 'nullable|array',
            'variants.*.color'            => 'nullable|string',
            'variants.*.storage'          => 'nullable|string',
            'variants.*.stock_quantity'   => 'nullable|integer|min:0',
            'variants.*.additional_price' => 'nullable|numeric|min:0',
        ]);

        $validated['slug'] = Str::slug($request->name);
        $validated['status'] = $request->boolean('status', true);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('products/thumbnails', 'public');
        }

        $product = Product::create($validated);

        if (!empty($validated['variants'])) {
            foreach ($validated['variants'] as $variant) {
                ProductVariant::create(array_merge($variant, ['product_id' => $product->id, 'status' => true]));
            }
        }

        return response()->json($product->load(['category', 'brand', 'variants']), 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load(['category', 'brand', 'images', 'variants']));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'category_id'    => 'required|exists:categories,id',
            'brand_id'       => 'required|exists:brands,id',
            'description'    => 'required|string',
            'price'          => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'thumbnail'      => 'nullable|image|max:2048',
            'status'         => 'boolean',
        ]);

        $validated['slug'] = Str::slug($request->name);
        $validated['status'] = $request->boolean('status', true);

        if ($request->hasFile('thumbnail')) {
            if ($product->thumbnail) {
                Storage::disk('public')->delete($product->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('products/thumbnails', 'public');
        }

        $product->update($validated);

        return response()->json($product->load(['category', 'brand', 'variants']));
    }

    public function destroy(Product $product)
    {
        if ($product->thumbnail) {
            Storage::disk('public')->delete($product->thumbnail);
        }
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }
        $product->delete();

        return response()->json(['message' => 'Xóa sản phẩm thành công']);
    }
}

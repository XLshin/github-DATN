<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Imei;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'brand', 'variants'])
            ->withCount('variants')
            ->latest()
            ->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'category_id'  => 'required|exists:categories,id',
            'brand_id'     => 'required|exists:brands,id',
            'description'  => 'required|string',
            'thumbnail'    => 'nullable|image|max:2048',
            'status'       => 'boolean',
            'images.*'     => 'nullable|image|max:2048',
            'product_type' => 'required|in:quantity,imei/serial',
            'variants'     => 'required|array|min:1',
            'variants.*.color'   => 'required|string|max:100',
            'variants.*.storage' => 'nullable|string|max:100',
            'variants.*.stock_quantity'   => 'nullable|integer|min:0',
            'variants.*.additional_price' => 'nullable|numeric|min:0',
        ]);

        $validated['slug']           = Str::slug($request->name);
        $validated['status']         = $request->boolean('status', true);
        $validated['price']          = 0;
        $validated['stock_quantity'] = 0;

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('products/thumbnails', 'public');
        }

        $product = Product::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/images', 'public');
                ProductImage::create(['product_id' => $product->id, 'image_path' => $path]);
            }
        }

        foreach ($request->variants as $variant) {
            $productVariant = ProductVariant::create([
                'product_id'       => $product->id,
                'color'            => $variant['color'],
                'storage'          => $variant['storage'] ?? '',
                'stock_quantity'   => $variant['stock_quantity'] ?? 0,
                'additional_price' => $variant['additional_price'] ?? 0,
                'status'           => true,
            ]);

            // Nếu là imei/serial, tạo các bản ghi IMEI từ textarea
            if ($validated['product_type'] === 'imei/serial' && !empty($variant['imeis'])) {
                $imeiList = array_filter(array_map('trim', explode("\n", $variant['imeis'])));
                foreach ($imeiList as $imeiCode) {
                    if ($imeiCode !== '') {
                        Imei::create([
                            'product_variant_id' => $productVariant->id,
                            'imei'               => $imeiCode,
                            'status'             => 'available',
                        ]);
                    }
                }
                // Cập nhật stock_quantity theo số IMEI thực tế
                $productVariant->update(['stock_quantity' => count($imeiList)]);
            }
        }

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'images', 'variants']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        $product->load(['images', 'variants']);
        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'category_id'  => 'required|exists:categories,id',
            'brand_id'     => 'required|exists:brands,id',
            'description'  => 'required|string',
            'thumbnail'    => 'nullable|image|max:2048',
            'status'       => 'boolean',
            'images.*'     => 'nullable|image|max:2048',
            'product_type' => 'required|in:quantity,imei/serial',
        ]);

        $validated['slug']   = Str::slug($request->name);
        $validated['status'] = $request->boolean('status', true);

        if ($request->hasFile('thumbnail')) {
            if ($product->thumbnail) {
                Storage::disk('public')->delete($product->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('products/thumbnails', 'public');
        }

        $product->update($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/images', 'public');
                ProductImage::create(['product_id' => $product->id, 'image_path' => $path]);
            }
        }

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Cập nhật sản phẩm thành công!');
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

        return redirect()->route('admin.products.index')
            ->with('success', 'Xóa sản phẩm thành công!');
    }

    public function destroyImage(ProductImage $image)
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();
        return back()->with('success', 'Đã xóa ảnh.');
    }

    public function showVariant(ProductVariant $variant)
    {
        $variant->load(['product.category', 'product.brand']);
        return view('admin.products.variant-show', compact('variant'));
    }

    public function updateVariant(Request $request, ProductVariant $variant)
    {
        $validated = $request->validate([
            'color'            => 'required|string|max:100',
            'storage'          => 'required|string|max:100',
            'stock_quantity'   => 'nullable|integer|min:0',
            'additional_price' => 'nullable|numeric|min:0',
            'status'           => 'boolean',
        ]);

        $validated['status']           = $request->boolean('status', false);
        $validated['additional_price'] = $validated['additional_price'] ?? 0;

        // Nếu là imei/serial và có nhập imeis mới, thêm vào danh sách
        if ($variant->product->product_type === 'imei/serial' && $request->filled('imeis')) {
            $imeiList = array_filter(array_map('trim', explode("\n", $request->imeis)));
            foreach ($imeiList as $imeiCode) {
                if ($imeiCode !== '') {
                    Imei::firstOrCreate(
                        ['imei' => $imeiCode],
                        ['product_variant_id' => $variant->id, 'status' => 'available']
                    );
                }
            }
            // Cập nhật stock_quantity theo số IMEI available
            $validated['stock_quantity'] = $variant->imeis()->where('status', 'available')->count();
        } else {
            $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        }

        $variant->update($validated);

        return redirect()->route('admin.products.show', $variant->product_id)
            ->with('success', 'Cập nhật biến thể thành công!');
    }
}

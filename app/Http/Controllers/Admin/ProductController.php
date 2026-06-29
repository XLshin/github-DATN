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
    public function index(Request $request)
    {
        $query = Product::with([
                'category',
                'brand',
                'variants.imeis'
            ])
            ->withCount('variants')
            ->orderBy('id', 'asc');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status === 'active');
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::all();
        $brands = Brand::all();

        return view('admin.products.index', compact('products', 'categories', 'brands'));
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
            'variants.*.imeis'   => 'nullable|string',
            'variants.*.images'  => 'nullable|array',
            'variants.*.images.*'=> 'nullable|image|max:2048',
        ]);

        $validated['slug']           = Str::slug($request->name);
        $validated['status']         = $request->boolean('status', false);
        $validated['price']          = 0;

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
            $variantData = [
                'product_id'       => $product->id,
                'color'            => $variant['color'],
                'storage'          => $variant['storage'] ?? '',
                'stock_quantity'   => $variant['stock_quantity'] ?? 0,
                'additional_price' => $variant['additional_price'] ?? 0,
                'status'           => true,
            ];

            $paths = [];
            $variantFiles = $variant['images'] ?? [];
            if (!empty($variantFiles) && is_array($variantFiles)) {
                foreach ($variantFiles as $file) {
                    if ($file) {
                        $paths[] = $file->store('products/variants', 'public');
                    }
                }
                if (!empty($paths)) {
                    $variantData['image_path'] = $paths[0];
                }
            }

            $productVariant = ProductVariant::create($variantData);

            if (count($paths) > 1) {
                foreach (array_slice($paths, 1) as $path) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'product_variant_id' => $productVariant->id,
                        'image_path' => $path,
                    ]);
                }
            }

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

        return redirect()->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'brand',
            'images',
            'variants.imeis'
        ]);
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
            'name'               => 'required|string|max:255',
            'category_id'        => 'required|exists:categories,id',
            'brand_id'           => 'required|exists:brands,id',
            'description'        => 'required|string',
            'thumbnail'          => 'nullable|image|max:2048',
            'status'             => 'boolean',
            'images.*'           => 'nullable|image|max:2048',
            'product_type'       => 'required|in:quantity,imei/serial',
            'stock_quantity'     => 'required|integer|min:0',
            'variants'           => 'nullable|array',
            'variants.*.color'   => 'required_with:variants|string|max:100',
            'variants.*.storage' => 'nullable|string|max:100',
            'variants.*.stock_quantity'   => 'nullable|integer|min:0',
            'variants.*.additional_price' => 'nullable|numeric|min:0',
            'variants.*.imeis'   => 'nullable|string',
            'variants.*.images'  => 'nullable|array',
            'variants.*.images.*'=> 'nullable|image|max:2048',
        ]);

        $validated['slug']   = Str::slug($request->name);
        $validated['status'] = $request->boolean('status', false);

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

        if (!empty($validated['variants'])) {
            foreach ($validated['variants'] as $variantData) {
                $variantFiles = $variantData['images'] ?? [];
                $variantInfo = [
                    'product_id'       => $product->id,
                    'color'            => $variantData['color'],
                    'storage'          => $variantData['storage'] ?? '',
                    'stock_quantity'   => $variantData['stock_quantity'] ?? 0,
                    'additional_price' => $variantData['additional_price'] ?? 0,
                    'status'           => true,
                ];

                $paths = [];
                if (!empty($variantFiles) && is_array($variantFiles)) {
                    foreach ($variantFiles as $file) {
                        if ($file) {
                            $paths[] = $file->store('products/variants', 'public');
                        }
                    }
                    if (!empty($paths)) {
                        $variantInfo['image_path'] = $paths[0];
                    }
                }

                $variant = ProductVariant::create($variantInfo);

                if (!empty($paths) && count($paths) > 1) {
                    foreach (array_slice($paths, 1) as $path) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'product_variant_id' => $variant->id,
                            'image_path' => $path,
                        ]);
                    }
                }

                if ($validated['product_type'] === 'imei/serial' && !empty($variantData['imeis'])) {
                    $imeiList = array_filter(array_map('trim', explode("\n", $variantData['imeis'])));
                    foreach ($imeiList as $imeiCode) {
                        if ($imeiCode !== '') {
                            Imei::create([
                                'product_variant_id' => $variant->id,
                                'imei'               => $imeiCode,
                                'status'             => 'available',
                            ]);
                        }
                    }
                    $variant->update(['stock_quantity' => count($imeiList)]);
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm thành công!');
    }

    public function destroy(Product $product)
    {
        if ($product->thumbnail) {
            Storage::disk('public')->delete($product->thumbnail);
        }

        foreach ($product->variants as $variant) {
            if ($variant->image_path) {
                Storage::disk('public')->delete($variant->image_path);
            }
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
        $variant->load(['product.category', 'product.brand', 'imeis', 'images']);
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
            'images'           => 'nullable|array',
            'images.*'         => 'nullable|image|max:2048',
        ]);

        $validated['status']           = $request->boolean('status', false);
        $validated['additional_price'] = $validated['additional_price'] ?? 0;

        if ($request->hasFile('images')) {
            if ($variant->image_path) {
                Storage::disk('public')->delete($variant->image_path);
            }

            $paths = [];
            foreach ($request->file('images') as $file) {
                $paths[] = $file->store('products/variants', 'public');
            }

            if (!empty($paths)) {
                $validated['image_path'] = $paths[0];
            }

            if (count($paths) > 1) {
                foreach (array_slice($paths, 1) as $path) {
                    ProductImage::create([
                        'product_id' => $variant->product_id,
                        'product_variant_id' => $variant->id,
                        'image_path' => $path,
                    ]);
                }
            }
        }

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

        return redirect()->route('admin.products.index')
            ->with('success', 'Cập nhật biến thể thành công!');
    }

    public function destroyVariant(ProductVariant $variant)
    {
        if ($variant->image_path) {
            Storage::disk('public')->delete($variant->image_path);
        }

        foreach ($variant->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $variant->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Đã xóa biến thể.');
    }
}

<?php

namespace App\Http\Controllers\Admin;
use App\Models\InventoryTransaction;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Imei;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    private function requiresStorage(?string $productType): bool
    {
        return strtolower((string) $productType) === 'imei/serial';
    }

    public function index(Request $request)
    {
        $query = Product::with([
                'productGroup',
                'category',
                'brand',
                'variants'
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

        if ($request->filled('product_type')) {
            $query->where('product_type', $request->product_type);
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
        $productGroups = ProductGroup::with(['category', 'brand'])->orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'brands', 'productGroups'));
    }

    public function store(Request $request)
    {
    DB::beginTransaction();
    try{
        $productGroup = ProductGroup::find($request->input('product_group_id'));
        $groupProductType = $productGroup?->product_type;
        $requiresStorage = $this->requiresStorage($groupProductType);
        $storageRules = ['nullable', 'string', 'max:255'];
        if ($requiresStorage) {
            $storageRules = ['required', 'string', 'max:255'];
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'product_group_id' => 'required|exists:product_groups,id',
            'description'  => 'required|string',
            'thumbnail'    => 'nullable|image|max:2048',
            'status'       => 'boolean',
            'images.*'     => 'nullable|image|max:2048',
            'variants'     => 'required|array|min:1',
            'variants.*.color'   => 'required|string|max:100',
            'variants.*.stock_quantity'   => 'nullable|integer|min:0',
            'variants.*.additional_price' => 'nullable|numeric|min:1',
            'variants.*.imeis'   => 'nullable|string',
            'variants.*.images'  => 'nullable|array',
            'variants.*.images.*'=> 'nullable|image|max:2048',
        ], [
            'variants.required'              => 'Sản phẩm phải có ít nhất một biến thể.',
            'variants.min'                   => 'Sản phẩm phải có ít nhất một biến thể.',
            'variants.*.color.required'      => 'Màu sắc biến thể không được để trống.',
            'variants.*.additional_price.min'=> 'Giá của biến thể phải lớn hơn 0.',
        ]);

        if (!$requiresStorage) {
            $validated['storage'] = null;
        }
        $validated['category_id'] = $productGroup->category_id;
        $validated['brand_id'] = $productGroup->brand_id;
        $validated['product_type'] = $productGroup->product_type;

        $validated['slug']           = Str::slug($request->name);
        $validated['status']         = $request->boolean('status', false);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('products/thumbnails', 'public');
        }

        $product = Product::create([
            'product_group_id' => $validated['product_group_id'],

            'name'=>$validated['name'],

            'category_id'=>$validated['category_id'],

            'storage' => $validated['storage'] ?? null,

            'brand_id'=>$validated['brand_id'],

            'description'=>$validated['description'],

            'thumbnail'=>$validated['thumbnail'] ?? null,

            'slug'=>$validated['slug'],

            'status'=>$validated['status'],

            'price' => $validated['price'] ?? 0,

            'product_type'=>$validated['product_type'],
        ]);

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
// Nếu sản phẩm quản lý theo số lượng thì tạo lịch sử nhập kho ban đầu
            if (
                $validated['product_type'] === 'quantity'
                && $productVariant->stock_quantity > 0
            ) {
                InventoryTransaction::create([
                    'product_variant_id' => $productVariant->id,
                    'type'               => 'import',
                    'quantity'           => $productVariant->stock_quantity,
                    'note'               => 'Nhập kho ban đầu khi tạo sản phẩm',
                ]);
            }
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
                        validator(
                                ['imei' => $imeiCode],
                                [
                                    'imei' => 'required|digits:15|unique:imeis,imei'
                                ]
                            )->validate();
                            Imei::create([
                                'product_variant_id' => $productVariant->id,
                                'imei'               => $imeiCode,
                                'status'             => 'available', 
                            ]);
                    }
                }
                // Cập nhật stock_quantity theo số IMEI thực tế
                $productVariant->update(['stock_quantity' => count($imeiList)]);
                if (count($imeiList) > 0) {
                    InventoryTransaction::create([
                        'product_variant_id' => $productVariant->id,
                        'type'               => 'import',
                        'quantity'           => count($imeiList),
                        'note'               => 'Nhập kho ban đầu (' . count($imeiList) . ' IMEI)',
                    ]);
                }
            }
        }
            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();

            throw $e;
        }
        return redirect()->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công!');
    
    }

    public function show(Product $product)
    {
        $product->load([
            'productGroup',
            'category',
            'brand',
            'images',
            'productGroup.specifications',
            'variants.imeis'
        ]);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        $productGroups=ProductGroup::with(['category', 'brand'])->orderBy('name')->get();
        $product->load(['images', 'variants']);
        return view('admin.products.edit', compact('product', 'categories', 'brands', 'productGroups'));
    }

    public function update(Request $request, Product $product)
    {
        $productGroup = ProductGroup::find($request->input('product_group_id'));
        $groupProductType = $productGroup?->product_type;
        $requiresStorage = $this->requiresStorage($groupProductType);
        $storageRules = ['nullable', 'string', 'max:255'];
        if ($requiresStorage) {
            $storageRules = ['required', 'string', 'max:255'];
        }

        $validated = $request->validate([
            'name'               => 'required|string|max:255|unique:products,name,' . $product->id,
            'product_group_id'   => 'required|exists:product_groups,id',
            'description'        => 'required|string',
            'thumbnail'          => 'nullable|image|max:2048',
            'status'             => 'boolean',
            'images.*'           => 'nullable|image|max:2048',
            'price'              =>'required|numeric|min:0',
            'variants'           => 'nullable|array',
            'variants.*.color'   => 'required_with:variants|string|max:100',
            'variants.*.stock_quantity'   => 'nullable|integer|min:0',
            'storage'            => $storageRules,
            'variants.*.additional_price' => 'nullable|numeric|min:0',
            'variants.*.imeis'   => 'nullable|string',
            'variants.*.images'  => 'nullable|array',
            'variants.*.images.*'=> 'nullable|image|max:2048',
        ], [
            'variants.*.color.required_with' => 'Màu sắc biến thể không được để trống.',
            'variants.*.additional_price.min'=> 'Giá của biến thể phải lớn hơn 0.',
        ]);

        if (!$requiresStorage) {
            $validated['storage'] = null;
        }
        $validated['category_id'] = $productGroup->category_id;
        $validated['brand_id'] = $productGroup->brand_id;
        $validated['product_type'] = $productGroup->product_type;

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
                }

                if ($existingVariant) {
                    // Biến thể đã tồn tại: cộng thêm tồn kho
                    if ($validated['product_type'] === 'quantity' && $addQty > 0) {
                        $existingVariant->increment('stock_quantity', $addQty);
                        InventoryTransaction::create([
                            'product_variant_id' => $existingVariant->id,
                            'type'               => 'import',
                            'quantity'           => $addQty,
                            'note'               => 'Nhập thêm tồn kho qua form sửa sản phẩm',
                        ]);
                    }

                    if (!empty($paths)) {
                        if (!$existingVariant->image_path) {
                            $existingVariant->update(['image_path' => $paths[0]]);
                        }
                        foreach (array_slice($paths, $existingVariant->image_path ? 0 : 1) as $path) {
                            ProductImage::create([
                                'product_id'         => $product->id,
                                'product_variant_id' => $existingVariant->id,
                                'image_path'         => $path,
                            ]);
                        }
                    }

                    $variant = $existingVariant;
                } else {
                    // Biến thể mới: tạo mới
                    $variantInfo = [
                        'product_id'       => $product->id,
                        'color'            => $color,
                        'storage'          => $storage,
                        'stock_quantity'   => $validated['product_type'] === 'quantity' ? $addQty : 0,
                        'additional_price' => $variantData['additional_price'] ?? 0,
                        'status'           => true,
                    ];

                    if (!empty($paths)) {
                        $variantInfo['image_path'] = $paths[0];
                    }

                    $variant = ProductVariant::create($variantInfo);

                    if ($validated['product_type'] === 'quantity' && $addQty > 0) {
                        InventoryTransaction::create([
                            'product_variant_id' => $variant->id,
                            'type'               => 'import',
                            'quantity'           => $addQty,
                            'note'               => 'Nhập kho ban đầu khi thêm biến thể mới',
                        ]);
                    }

                    if (count($paths) > 1) {
                        foreach (array_slice($paths, 1) as $path) {
                            ProductImage::create([
                                'product_id'         => $product->id,
                                'product_variant_id' => $variant->id,
                                'image_path'         => $path,
                            ]);
                        }
                    }
                }

                if ($validated['product_type'] === 'imei/serial' && !empty($variantData['imeis'])) {
                    $imeiList = array_filter(array_map('trim', explode("\n", $variantData['imeis'])));
                    foreach ($imeiList as $imeiCode) {
                        if ($imeiCode !== '') {
                            Imei::firstOrCreate(
                                ['imei' => $imeiCode],
                                ['product_variant_id' => $variant->id, 'status' => 'available']
                            );
                        }
                    }
                    $variant->update([
                        'stock_quantity' => $variant->imeis()->where('status', 'available')->count(),
                    ]);
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
        if ($variant->product->variants()->count() <= 1) {
            return back()->with('error', 'Không thể xóa biến thể duy nhất của sản phẩm.');
        }

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

    public function ajaxStore(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_groups,name',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'product_type' => 'required|in:quantity,imei/serial',
            'description' => 'nullable|string',
        ]);
        $group = ProductGroup::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'product_type' => $request->product_type,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json($group->load(['category', 'brand']));
    }
}

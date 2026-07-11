<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ProductGroupController extends Controller
{
    private function syncSpecifications(ProductGroup $productGroup, array $specifications = []): void
    {
        $rows = [];

        foreach (array_values($specifications) as $index => $specification) {
            $name = trim((string) ($specification['name'] ?? ''));
            $value = trim((string) ($specification['value'] ?? ''));

            if ($name === '' || $value === '') {
                continue;
            }

            $rows[] = [
                'group_name' => trim((string) ($specification['group_name'] ?? '')) ?: null,
                'name' => $name,
                'value' => $value,
                'sort_order' => $index,
            ];
        }

        $productGroup->specifications()->delete();

        if (!empty($rows)) {
            $productGroup->specifications()->createMany($rows);
        }
    }

    public function index(Request $request)
    {
        $query = ProductGroup::with([
                'category',
                'brand',
                'images',
                'products' => fn ($productQuery) => $productQuery
                    ->with('variants')
                    ->withCount('variants')
                    ->orderBy('storage')
                    ->orderBy('name'),
            ])
            ->withCount('products')
            ->orderBy('name');

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
            $query->where('status', $request->status);
        }

        $productGroups = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.product-groups.index', compact('productGroups', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $specificationSourceGroups = ProductGroup::whereHas('specifications')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.product-groups.create', compact('categories', 'brands', 'specificationSourceGroups'));
    }

    public function show(ProductGroup $productGroup)
    {
        $productGroup->load([
            'category',
            'brand',
            'images',
            'specifications',
            'products' => fn ($query) => $query
                ->with([
                    'variants' => fn ($variantQuery) => $variantQuery
                        ->with([
                            'images',
                            'imeis' => fn ($imeiQuery) => $imeiQuery->orderBy('imei'),
                        ])
                        ->withCount([
                            'imeis',
                            'imeis as available_imeis_count' => fn ($imeiQuery) => $imeiQuery->where('status', 'available'),
                            'imeis as sold_imeis_count' => fn ($imeiQuery) => $imeiQuery->where('status', 'sold'),
                            'imeis as reserved_imeis_count' => fn ($imeiQuery) => $imeiQuery->where('status', 'reserved'),
                        ]),
                ])
                ->withCount('variants')
                ->orderBy('storage')
                ->orderBy('name'),
        ]);

        return view('admin.product-groups.show', compact('productGroup'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'product_type' => trim((string) $request->input('product_type')),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_groups,name',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'product_type' => 'required|in:quantity,imei/serial',
            'description' => 'nullable|string',
            'status' => 'boolean',
            'product_thumbnail' => 'nullable|image|max:2048',
            'product_images' => 'nullable|array',
            'product_images.*' => 'nullable|image|max:2048',
            'specifications' => 'nullable|array',
            'specifications.*.group_name' => 'nullable|string|max:255',
            'specifications.*.name' => 'nullable|string|max:255',
            'specifications.*.value' => 'nullable|string',
            'versions' => 'required|array|min:1',
            'versions.*.storage' => 'required|string|max:255',
            'versions.*.name' => 'required|string|max:255|distinct|unique:products,name',
            'versions.*.price' => 'required|numeric|min:0',
            'versions.*.description' => 'nullable|string',
            'colors' => 'required|array|min:1',
            'colors.*.name' => 'required|string|max:100|distinct',
            'colors.*.image' => 'nullable|image|max:2048',
        ], [
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên sản phẩm này đã tồn tại.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists' => 'Danh mục đã chọn không hợp lệ.',
            'brand_id.required' => 'Vui lòng chọn thương hiệu.',
            'brand_id.exists' => 'Thương hiệu đã chọn không hợp lệ.',
            'product_type.required' => 'Vui lòng chọn loại quản lý.',
            'product_type.in' => 'Loại quản lý đã chọn không hợp lệ.',
            'description.string' => 'Mô tả sản phẩm không hợp lệ.',
            'status.boolean' => 'Trạng thái hiển thị không hợp lệ.',
            'product_thumbnail.image' => 'Ảnh đại diện sản phẩm phải là tệp hình ảnh.',
            'product_thumbnail.max' => 'Ảnh đại diện sản phẩm không được vượt quá 2MB.',
            'product_images.array' => 'Danh sách ảnh bổ sung không hợp lệ.',
            'product_images.*.image' => 'Ảnh bổ sung sản phẩm phải là tệp hình ảnh.',
            'product_images.*.max' => 'Ảnh bổ sung sản phẩm không được vượt quá 2MB.',
            'specifications.array' => 'Danh sách thông số kỹ thuật không hợp lệ.',
            'specifications.*.group_name.string' => 'Nhóm thông số không hợp lệ.',
            'specifications.*.group_name.max' => 'Nhóm thông số không được vượt quá 255 ký tự.',
            'specifications.*.name.string' => 'Tên thông số không hợp lệ.',
            'specifications.*.name.max' => 'Tên thông số không được vượt quá 255 ký tự.',
            'specifications.*.value.string' => 'Giá trị thông số không hợp lệ.',
            'versions.required' => 'Vui lòng thêm ít nhất một phiên bản sản phẩm.',
            'versions.array' => 'Danh sách phiên bản không hợp lệ.',
            'versions.min' => 'Vui lòng thêm ít nhất một phiên bản sản phẩm.',
            'versions.*.storage.required' => 'Vui lòng nhập dung lượng hoặc tên phiên bản.',
            'versions.*.storage.max' => 'Dung lượng hoặc tên phiên bản không được vượt quá 255 ký tự.',
            'versions.*.name.required' => 'Vui lòng nhập tên phiên bản.',
            'versions.*.name.max' => 'Tên phiên bản không được vượt quá 255 ký tự.',
            'versions.*.name.distinct' => 'Tên phiên bản không được trùng nhau.',
            'versions.*.name.unique' => 'Tên phiên bản này đã tồn tại.',
            'versions.*.price.required' => 'Vui lòng nhập giá base cho phiên bản.',
            'versions.*.price.numeric' => 'Giá base phải là số.',
            'versions.*.price.min' => 'Giá base không được nhỏ hơn 0.',
            'versions.*.description.string' => 'Mô tả phiên bản không hợp lệ.',
            'colors.required' => 'Vui lòng thêm ít nhất một màu sắc.',
            'colors.array' => 'Danh sách màu sắc không hợp lệ.',
            'colors.min' => 'Vui lòng thêm ít nhất một màu sắc.',
            'colors.*.name.required' => 'Vui lòng nhập tên màu.',
            'colors.*.name.max' => 'Tên màu không được vượt quá 100 ký tự.',
            'colors.*.name.distinct' => 'Tên màu không được trùng nhau.',
            'colors.*.image.image' => 'Ảnh màu phải là tệp hình ảnh.',
            'colors.*.image.max' => 'Ảnh màu không được vượt quá 2MB.',
        ]);

        $specifications = $validated['specifications'] ?? [];
        $versions = $validated['versions'] ?? [];
        $colors = $validated['colors'] ?? [];
        unset($validated['specifications']);
        unset($validated['versions']);
        unset($validated['colors']);
        unset($validated['product_thumbnail']);
        unset($validated['product_images']);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['status'] = $request->boolean('status', false);

        DB::transaction(function () use ($request, $validated, $specifications, $versions, $colors) {
            $productGroup = ProductGroup::create($validated);
            $productGroup->refresh();

            if ($productGroup->product_type !== $validated['product_type']) {
                throw ValidationException::withMessages([
                    'product_type' => 'Loại quản lý lưu xuống cơ sở dữ liệu không khớp với lựa chọn. Vui lòng thử lại.',
                ]);
            }

            $this->syncSpecifications($productGroup, $specifications);

            if ($request->hasFile('product_thumbnail')) {
                ProductImage::create([
                    'product_group_id' => $productGroup->id,
                    'image_path' => $request->file('product_thumbnail')
                        ->store('products/groups', 'public'),
                ]);
            }

            if ($request->hasFile('product_images')) {
                foreach ($request->file('product_images') as $image) {
                    if (!$image) {
                        continue;
                    }

                    ProductImage::create([
                        'product_group_id' => $productGroup->id,
                        'image_path' => $image->store('products/groups', 'public'),
                    ]);
                }
            }

            foreach ($versions as $version) {
                $product = Product::create([
                    'product_group_id' => $productGroup->id,
                    'category_id' => $productGroup->category_id,
                    'brand_id' => $productGroup->brand_id,
                    'product_type' => $validated['product_type'],
                    'storage' => $version['storage'],
                    'name' => $version['name'],
                    'slug' => Str::slug($version['name']),
                    'description' => $version['description'] ?? null,
                    'price' => $version['price'],
                    'stock_quantity' => 0,
                    'thumbnail' => null,
                    'status' => $validated['status'],
                ]);

                foreach ($colors as $colorIndex => $color) {
                    $variantImage = null;
                    if ($request->hasFile("colors.$colorIndex.image")) {
                        $variantImage = $request->file("colors.$colorIndex.image")
                            ->store('products/variants', 'public');
                    }

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'color' => $color['name'],
                        'image_path' => $variantImage,
                        'stock_quantity' => 0,
                        'additional_price' => 0,
                        'status' => true,
                    ]);
                }
            }
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công.');
    }

    public function edit(ProductGroup $productGroup)
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $specificationSourceGroups = ProductGroup::whereHas('specifications')
            ->whereKeyNot($productGroup->id)
            ->orderBy('name')
            ->get(['id', 'name']);
        $productGroup->loadCount('products');
        $productGroup->load([
            'specifications',
            'images',
            'products' => fn ($query) => $query
                ->with([
                    'images',
                    'variants' => fn ($variantQuery) => $variantQuery
                        ->with('images')
                        ->withCount('imeis'),
                ])
                ->orderBy('storage')
                ->orderBy('name'),
        ]);

        return view('admin.product-groups.edit', compact('productGroup', 'categories', 'brands', 'specificationSourceGroups'));
    }

    public function specifications(ProductGroup $productGroup)
    {
        $productGroup->load('specifications');

        return response()->json(
            $productGroup->specifications->map(fn ($specification) => [
                'group_name' => $specification->group_name,
                'name' => $specification->name,
                'value' => $specification->value,
            ])->values()
        );
    }

    public function update(Request $request, ProductGroup $productGroup)
    {
        $request->merge([
            'product_type' => trim((string) $request->input('product_type')),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_groups,name,' . $productGroup->id,
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'product_type' => 'required|in:quantity,imei/serial',
            'description' => 'nullable|string',
            'status' => 'boolean',
            'product_thumbnail' => 'nullable|image|max:2048',
            'product_images' => 'nullable|array',
            'product_images.*' => 'nullable|image|max:2048',
            'specifications' => 'nullable|array',
            'specifications.*.group_name' => 'nullable|string|max:255',
            'specifications.*.name' => 'nullable|string|max:255',
            'specifications.*.value' => 'nullable|string',
            'versions' => 'required|array|min:1',
            'versions.*.id' => 'nullable|integer|exists:products,id',
            'versions.*.storage' => 'required|string|max:255',
            'versions.*.name' => 'required|string|max:255',
            'versions.*.price' => 'required|numeric|min:0',
            'versions.*.description' => 'nullable|string',
            'colors' => 'required|array|min:1',
            'colors.*.original_name' => 'nullable|string|max:100',
            'colors.*.name' => 'required|string|max:100',
            'colors.*.image' => 'nullable|image|max:2048',
            'colors.*.delete_image' => 'nullable|boolean',
        ], [
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên sản phẩm này đã tồn tại.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists' => 'Danh mục đã chọn không hợp lệ.',
            'brand_id.required' => 'Vui lòng chọn thương hiệu.',
            'brand_id.exists' => 'Thương hiệu đã chọn không hợp lệ.',
            'product_type.required' => 'Vui lòng chọn loại quản lý.',
            'product_type.in' => 'Loại quản lý đã chọn không hợp lệ.',
            'description.string' => 'Mô tả sản phẩm không hợp lệ.',
            'status.boolean' => 'Trạng thái hiển thị không hợp lệ.',
            'product_thumbnail.image' => 'Ảnh đại diện sản phẩm phải là tệp hình ảnh.',
            'product_thumbnail.max' => 'Ảnh đại diện sản phẩm không được vượt quá 2MB.',
            'product_images.array' => 'Danh sách ảnh bổ sung không hợp lệ.',
            'product_images.*.image' => 'Ảnh bổ sung sản phẩm phải là tệp hình ảnh.',
            'product_images.*.max' => 'Ảnh bổ sung sản phẩm không được vượt quá 2MB.',
            'specifications.array' => 'Danh sách thông số kỹ thuật không hợp lệ.',
            'versions.required' => 'Vui lòng thêm ít nhất một phiên bản sản phẩm.',
            'versions.array' => 'Danh sách phiên bản không hợp lệ.',
            'versions.min' => 'Vui lòng thêm ít nhất một phiên bản sản phẩm.',
            'versions.*.id.exists' => 'Phiên bản đã chọn không hợp lệ.',
            'versions.*.storage.required' => 'Vui lòng nhập dung lượng hoặc tên phiên bản.',
            'versions.*.storage.max' => 'Dung lượng hoặc tên phiên bản không được vượt quá 255 ký tự.',
            'versions.*.name.required' => 'Vui lòng nhập tên phiên bản.',
            'versions.*.name.max' => 'Tên phiên bản không được vượt quá 255 ký tự.',
            'versions.*.price.required' => 'Vui lòng nhập giá base cho phiên bản.',
            'versions.*.price.numeric' => 'Giá base phải là số.',
            'versions.*.price.min' => 'Giá base không được nhỏ hơn 0.',
            'versions.*.description.string' => 'Mô tả phiên bản không hợp lệ.',
            'colors.required' => 'Vui lòng thêm ít nhất một màu sắc.',
            'colors.array' => 'Danh sách màu sắc không hợp lệ.',
            'colors.min' => 'Vui lòng thêm ít nhất một màu sắc.',
            'colors.*.original_name.max' => 'Tên màu cũ không được vượt quá 100 ký tự.',
            'colors.*.name.required' => 'Vui lòng nhập tên màu.',
            'colors.*.name.max' => 'Tên màu không được vượt quá 100 ký tự.',
            'colors.*.image.image' => 'Ảnh màu phải là tệp hình ảnh.',
            'colors.*.image.max' => 'Ảnh màu không được vượt quá 2MB.',
            'colors.*.delete_image.boolean' => 'Tùy chọn xóa ảnh màu không hợp lệ.',
        ]);

        $specifications = $validated['specifications'] ?? [];
        $versions = $validated['versions'] ?? [];
        $colors = $validated['colors'] ?? [];
        unset($validated['specifications']);
        unset($validated['versions']);
        unset($validated['colors']);
        unset($validated['product_thumbnail']);
        unset($validated['product_images']);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['status'] = $request->boolean('status', false);

        if (
            $validated['product_type'] !== $productGroup->product_type
            && $this->productGroupHasInventory($productGroup)
        ) {
            return back()
                ->withErrors(['product_type' => 'Không thể đổi loại quản lý vì sản phẩm đã có IMEI hoặc tồn kho.'])
                ->withInput();
        }

        $this->validateVersionsAndColorsForUpdate($productGroup, $versions, $colors);

        DB::transaction(function () use ($request, $productGroup, $validated, $specifications, $versions, $colors) {
            $productGroup->update($validated);
            $productGroup->refresh();

            if ($productGroup->product_type !== $validated['product_type']) {
                throw ValidationException::withMessages([
                    'product_type' => 'Loại quản lý lưu xuống cơ sở dữ liệu không khớp với lựa chọn. Vui lòng thử lại.',
                ]);
            }

            $this->syncSpecifications($productGroup, $specifications);

            if ($request->hasFile('product_thumbnail')) {
                ProductImage::create([
                    'product_group_id' => $productGroup->id,
                    'image_path' => $request->file('product_thumbnail')
                        ->store('products/groups', 'public'),
                ]);
            }

            if ($request->hasFile('product_images')) {
                foreach ($request->file('product_images') as $image) {
                    if (!$image) {
                        continue;
                    }

                    ProductImage::create([
                        'product_group_id' => $productGroup->id,
                        'image_path' => $image->store('products/groups', 'public'),
                    ]);
                }
            }

            $submittedVersionIds = collect($versions)
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id);

            $productsToDelete = $productGroup->products()
                ->with([
                    'images',
                    'variants' => fn ($query) => $query->with('images')->withCount('imeis'),
                ])
                ->when($submittedVersionIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $submittedVersionIds))
                ->when($submittedVersionIds->isEmpty(), fn ($query) => $query)
                ->get();

            foreach ($productsToDelete as $productToDelete) {
                if ($productToDelete->variants->contains(fn ($variant) => (int) ($variant->imeis_count ?? 0) > 0)) {
                    throw ValidationException::withMessages([
                        'versions' => "Không thể xóa phiên bản {$productToDelete->name} vì đã có IMEI.",
                    ]);
                }

                $this->deleteProductFiles($productToDelete);
                $productToDelete->delete();
            }

            foreach ($versions as $version) {
                $payload = [
                    'product_group_id' => $productGroup->id,
                    'category_id' => $productGroup->category_id,
                    'brand_id' => $productGroup->brand_id,
                    'product_type' => $validated['product_type'],
                    'storage' => $version['storage'],
                    'name' => $version['name'],
                    'slug' => Str::slug($version['name']),
                    'description' => $version['description'] ?? null,
                    'price' => $version['price'],
                    'status' => $validated['status'],
                ];

                if (!empty($version['id'])) {
                    $productGroup->products()->whereKey($version['id'])->update($payload);
                    continue;
                }

                Product::create($payload + [
                    'stock_quantity' => 0,
                    'thumbnail' => null,
                ]);
            }

            $products = $productGroup->products()->with([
                'variants' => fn ($query) => $query->with('images')->withCount('imeis'),
            ])->get();
            $productIds = $products->pluck('id');
            $submittedOriginalColors = collect($colors)
                ->pluck('original_name')
                ->filter()
                ->map(fn ($color) => trim((string) $color))
                ->values();

            $colorsToDelete = ProductVariant::whereIn('product_id', $productIds)
                ->select('color')
                ->distinct()
                ->pluck('color')
                ->filter()
                ->reject(fn ($color) => $submittedOriginalColors->contains($color));

            foreach ($colorsToDelete as $colorToDelete) {
                $variantsToDelete = ProductVariant::whereIn('product_id', $productIds)
                    ->where('color', $colorToDelete)
                    ->with('images')
                    ->withCount('imeis')
                    ->get();

                if ($variantsToDelete->contains(fn ($variant) => (int) ($variant->imeis_count ?? 0) > 0)) {
                    throw ValidationException::withMessages([
                        'colors' => "Không thể xóa màu {$colorToDelete} vì đã có IMEI.",
                    ]);
                }

                foreach ($variantsToDelete as $variantToDelete) {
                    $this->deleteVariantFiles($variantToDelete);
                    $variantToDelete->delete();
                }
            }

            foreach ($colors as $colorIndex => $color) {
                $colorName = trim((string) $color['name']);
                $originalName = trim((string) ($color['original_name'] ?? ''));

                if ($originalName !== '' && $originalName !== $colorName) {
                    ProductVariant::whereIn('product_id', $productIds)
                        ->where('color', $originalName)
                        ->update(['color' => $colorName]);
                }

                foreach ($products as $product) {
                    $variant = ProductVariant::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'color' => $colorName,
                        ],
                        [
                            'image_path' => null,
                            'stock_quantity' => 0,
                            'additional_price' => 0,
                            'status' => true,
                        ]
                    );

                    $shouldDeleteColorImage = !empty($color['delete_image']) || $request->hasFile("colors.$colorIndex.image");

                    if ($shouldDeleteColorImage && $variant->image_path) {
                        Storage::disk('public')->delete($variant->image_path);
                        $variant->update(['image_path' => null]);
                    }

                    if ($request->hasFile("colors.$colorIndex.image")) {
                        if ($variant->image_path) {
                            Storage::disk('public')->delete($variant->image_path);
                        }

                        $variant->update([
                            'image_path' => $request->file("colors.$colorIndex.image")
                                ->store('products/variants', 'public'),
                        ]);
                    }
                }
            }

            $productGroup->products()->update([
                'category_id' => $productGroup->category_id,
                'brand_id' => $productGroup->brand_id,
                'product_type' => $validated['product_type'],
            ]);
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm thành công.');
    }

    public function updateVariantPrice(Request $request, ProductVariant $variant)
    {
        $validated = $request->validate([
            'additional_price' => 'nullable|numeric|min:0',
        ], [
            'additional_price.numeric' => 'Giá cộng thêm phải là số.',
            'additional_price.min' => 'Giá cộng thêm không được nhỏ hơn 0.',
        ]);

        $variant->update([
            'additional_price' => $validated['additional_price'] ?? 0,
        ]);

        return back()->with('success', 'Đã cập nhật giá cộng thêm của màu.');
    }

    private function validateVersionsAndColorsForUpdate(ProductGroup $productGroup, array $versions, array $colors): void
    {
        $versionIds = collect($versions)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($versionIds->isNotEmpty()) {
            $ownedVersionIds = $productGroup->products()
                ->whereIn('id', $versionIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();

            if ($ownedVersionIds->all() !== $versionIds->sort()->values()->all()) {
                throw ValidationException::withMessages([
                    'versions' => 'Có phiên bản không thuộc sản phẩm này.',
                ]);
            }
        }

        $versionNames = collect($versions)
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->values();

        if ($versionNames->count() !== $versionNames->unique()->count()) {
            throw ValidationException::withMessages([
                'versions' => 'Tên phiên bản không được trùng nhau.',
            ]);
        }

        $duplicatedProduct = Product::whereIn('name', $versionNames)
            ->when($versionIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $versionIds))
            ->first();

        if ($duplicatedProduct) {
            throw ValidationException::withMessages([
                'versions' => "Tên phiên bản {$duplicatedProduct->name} đã tồn tại.",
            ]);
        }

        $existingVersions = $productGroup->products()
            ->with(['variants' => fn ($query) => $query->withCount('imeis')])
            ->whereIn('id', $versionIds)
            ->get()
            ->keyBy('id');

        foreach ($versions as $index => $version) {
            if (empty($version['id'])) {
                continue;
            }

            $existingVersion = $existingVersions->get((int) $version['id']);
            $hasImei = $existingVersion
                && $existingVersion->variants->contains(fn ($variant) => (int) ($variant->imeis_count ?? 0) > 0);

            if (!$hasImei) {
                continue;
            }

            if (
                trim((string) $existingVersion->storage) !== trim((string) $version['storage'])
                || trim((string) $existingVersion->name) !== trim((string) $version['name'])
            ) {
                throw ValidationException::withMessages([
                    "versions.$index.name" => 'Phiên bản đã có IMEI nên không thể sửa tên hoặc dung lượng.',
                ]);
            }
        }

        $colorNames = collect($colors)
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->values();

        if ($colorNames->count() !== $colorNames->unique()->count()) {
            throw ValidationException::withMessages([
                'colors' => 'Tên màu không được trùng nhau.',
            ]);
        }

        $productIds = $productGroup->products()->pluck('id');

        foreach ($colors as $index => $color) {
            $originalName = trim((string) ($color['original_name'] ?? ''));
            $colorName = trim((string) ($color['name'] ?? ''));

            if ($originalName === '' || $originalName === $colorName) {
                continue;
            }

            $hasImei = ProductVariant::whereIn('product_id', $productIds)
                ->where('color', $originalName)
                ->whereHas('imeis')
                ->exists();

            if ($hasImei) {
                throw ValidationException::withMessages([
                    "colors.$index.name" => 'Màu đã có IMEI nên không thể đổi tên.',
                ]);
            }
        }
    }

    private function deleteProductFiles(Product $product): void
    {
        if ($product->thumbnail) {
            Storage::disk('public')->delete($product->thumbnail);
        }

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        foreach ($product->variants as $variant) {
            $this->deleteVariantFiles($variant);
            $variant->delete();
        }
    }

    private function deleteVariantFiles(ProductVariant $variant): void
    {
        if ($variant->image_path) {
            Storage::disk('public')->delete($variant->image_path);
        }

        foreach ($variant->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }
    }

    private function productGroupHasInventory(ProductGroup $productGroup): bool
    {
        return ProductVariant::whereHas('product', fn ($query) => $query->where('product_group_id', $productGroup->id))
            ->where(function ($query) {
                $query->where('stock_quantity', '>', 0)
                    ->orWhereHas('imeis');
            })
            ->exists();
    }

    public function destroy(ProductGroup $productGroup)
    {
        $productGroup->loadCount('products');

        if ($productGroup->products_count > 0) {
            return back()->with('error', 'Không thể xóa sản phẩm đang có phiên bản.');
        }

        $productGroup->delete();

        return back()->with('success', 'Đã xóa sản phẩm.');
    }
}

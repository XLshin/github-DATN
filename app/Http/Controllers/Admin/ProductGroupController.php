<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductGroupController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductGroup::with(['category', 'brand'])
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

        $productGroups = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.product-groups.index', compact('productGroups', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.product-groups.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_groups,name',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'product_type' => 'required|in:quantity,imei/serial',
            'description' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['status'] = $request->boolean('status', true);

        ProductGroup::create($validated);

        return redirect()->route('admin.product-groups.index')
            ->with('success', 'Thêm dòng sản phẩm thành công.');
    }

    public function edit(ProductGroup $productGroup)
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $productGroup->loadCount('products');

        return view('admin.product-groups.edit', compact('productGroup', 'categories', 'brands'));
    }

    public function update(Request $request, ProductGroup $productGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_groups,name,' . $productGroup->id,
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'product_type' => 'required|in:quantity,imei/serial',
            'description' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['status'] = $request->boolean('status', false);

        if (
            $validated['product_type'] !== $productGroup->product_type
            && $productGroup->products()->exists()
        ) {
            return back()
                ->withErrors(['product_type' => 'Khong the doi loai quan ly khi dong san pham da co san pham.'])
                ->withInput();
        }

        $productGroup->update($validated);
        $productGroup->products()->update([
            'category_id' => $productGroup->category_id,
            'brand_id' => $productGroup->brand_id,
            'product_type' => $productGroup->product_type,
        ]);

        return redirect()->route('admin.product-groups.index')
            ->with('success', 'Cập nhật dòng sản phẩm thành công.');
    }

    public function destroy(ProductGroup $productGroup)
    {
        $productGroup->loadCount('products');

        if ($productGroup->products_count > 0) {
            return back()->with('error', 'Không thể xóa dòng sản phẩm đang có sản phẩm.');
        }

        $productGroup->delete();

        return back()->with('success', 'Đã xóa dòng sản phẩm.');
    }
}

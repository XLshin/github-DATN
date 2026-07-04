<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Http\Requests\BrandRequest;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::with('categories')->paginate(10);
        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.brands.create', compact('categories'));
    }

    public function store(BrandRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand = Brand::create(\Arr::except($data, ['category_ids']));
        $brand->categories()->sync($data['category_ids'] ?? []);

        return redirect()->route('brands.index')->with('success', 'Thêm thương hiệu thành công');
    }

    public function show(Brand $brand)
    {
        $products = $brand->products()
            ->with(['category', 'images', 'variants'])
            ->withSum('variants as total_stock', 'stock_quantity')
            ->withSum([
                'orderItems as sold_quantity' => fn($q) => $q->whereHas(
                    'order', fn($o) => $o->whereIn('status', ['processing', 'shipping', 'completed'])
                )
            ], 'quantity')
            ->when(request('category_id'), fn($q) => $q->where('category_id', request('category_id')))
            ->paginate(12)
            ->withQueryString();

        // Danh mục có sản phẩm thuộc brand này
        $categories = Category::withCount([
            'products as products_count' => fn($q) => $q->where('brand_id', $brand->id)
        ])
        ->whereHas('products', fn($q) => $q->where('brand_id', $brand->id))
        ->get();

        return view('admin.brands.show', compact('brand', 'products', 'categories'));
    }

    public function edit(Brand $brand)
    {
        $categories = Category::orderBy('name')->get();
        $selectedCategoryIds = $brand->categories->pluck('id')->toArray();
        return view('admin.brands.edit', compact('brand', 'categories', 'selectedCategoryIds'));
    }

    public function update(BrandRequest $request, Brand $brand)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand->update(\Arr::except($data, ['category_ids']));
        $brand->categories()->sync($data['category_ids'] ?? []);

        return redirect()->route('brands.index')->with('success', 'Cập nhật thành công');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        return back()->with('success', 'Xóa thành công');
    }
}

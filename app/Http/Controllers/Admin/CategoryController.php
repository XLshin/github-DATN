<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Requests\CategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::when(
            $request->search,
            fn($q) => $q->where('name', 'like', '%' . $request->search . '%')
        )->paginate(10)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view(
            'admin.categories.create'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        Category::create(
            $request->validated()
        );

        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                'Thêm thành công'
            );
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        // Load brands có sản phẩm trong danh mục này
        $brands = \App\Models\Brand::withCount([
            'products as products_count' => fn($q) => $q->where('category_id', $category->id)
        ])
        ->whereHas('products', fn($q) => $q->where('category_id', $category->id))
        ->get();

        // Load sản phẩm trong danh mục, có thể lọc theo brand
        $products = $category->products()
            ->with(['brand', 'images', 'variants'])
            ->withSum('variants as total_stock', 'stock_quantity')
            ->withSum([
                'orderItems as sold_quantity' => fn($q) => $q->whereHas(
                    'order', fn($o) => $o->whereIn('status', ['processing', 'shipping', 'completed'])
                )
            ], 'quantity')
            ->when(request('brand_id'), fn($q) => $q->where('brand_id', request('brand_id')))
            ->paginate(12)
            ->withQueryString();

        return view('admin.categories.show', compact('category', 'brands', 'products'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view(
            'admin.categories.edit',
            compact('category')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update(
            $request->validated()
        );

        return redirect()
            ->route('categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return back();
    }
}

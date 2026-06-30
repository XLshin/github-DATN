<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Imei;
use Illuminate\Validation\Rule;
use App\Models\ProductVariant;
use App\Models\InventoryTransaction;

class ImeiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $phoneCategoryName = 'Điện thoại';

        $query = Imei::query()
            ->whereHas('productVariant.product.category', function ($query) use ($phoneCategoryName) {
                $query->where('name', $phoneCategoryName);
            });

        if ($request->keyword)
        {
            $query->where(
                'imei',
                'like',
                '%' . $request->keyword . '%'
            );
        }
        if($request->variant_id)
        {
            $query->where(
                'product_variant_id',
                $request->variant_id
            );
        }
        if($request->status)
        {
            $query->where('status', $request->status);
        }

        $imeis = $query
            ->with('productVariant.product')
            ->paginate(10);

        return view(
            'admin.imeis.index',
            compact('imeis')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $imeiVariants = ProductVariant::with('product')
            ->whereHas('product', function ($query) {
                $query->where('product_type', 'imei/serial');
            })
            ->get();

        return view(
            'admin.imeis.create',
            compact('imeiVariants')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'imeis' => 'required|string',
        ], [
            'product_variant_id.required' => 'Bạn phải chọn biến thể sản phẩm.',
            'product_variant_id.exists' => 'Biến thể sản phẩm không tồn tại.',
            'imeis.required' => 'Bạn phải nhập IMEI/Serial.',
        ]);

        $variant = ProductVariant::with('product')
            ->findOrFail($request->product_variant_id);

        if ($variant->product->product_type !== 'imei/serial') {
            return back()
                ->withErrors([
                    'product_variant_id' => 'Sản phẩm này không quản lý bằng IMEI/Serial.'
                ])
                ->withInput();
        }

        $imeis = preg_split(
            '/\r\n|\r|\n/',
            trim($request->imeis)
        );

        $imeis = array_filter(
            array_map('trim', $imeis)
        );

        if (count($imeis) !== count(array_unique($imeis))) {
            return back()
                ->withErrors([
                    'imeis' => 'Danh sách IMEI/Serial bị trùng.'
                ])
                ->withInput();
        }

        foreach ($imeis as $imei) {

            validator(
                ['imei' => $imei],
                [
                    'imei' => 'required|digits:15|unique:imeis,imei'
                ],
                [
                    'imei.required' => 'IMEI không được để trống.',
                    'imei.digits' => 'IMEI phải gồm đúng 15 chữ số.',
                    'imei.unique' => "IMEI {$imei} đã tồn tại."
                ]
            )->validate();

            Imei::create([
                'product_variant_id' => $variant->id,
                'imei' => $imei,
                'status' => 'available',
            ]);
        }

        InventoryTransaction::create([
            'product_variant_id' => $variant->id,
            'type' => 'import',
            'quantity' => count($imeis),
            'note' => 'Nhập kho bằng IMEI/Serial',
        ]);

        return redirect()
            ->route('admin.stocks')
            ->with(
                'success',
                'Đã nhập ' . count($imeis) . ' IMEI thành công'
            );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $imei = Imei::with([
            'productVariant.product.brand',
            'orderItem.order.user',
            'warranty'
        ])->findOrFail($id);

        return view(
            'admin.imeis.show',
            compact('imei')
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $imei = Imei::findOrFail($id);

        return view('admin.imeis.edit', compact('imei'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $imei = Imei::findOrFail($id);

        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',

            'imei' => [
                'required',
                'digits:15',
                Rule::unique('imeis', 'imei')->ignore($id),
            ],

            'status' => [
                'required',
                Rule::in(['available', 'sold', 'warranty']),
            ],
        ], [
            'product_variant_id.required' => 'Bạn phải chọn biến thể sản phẩm.',
            'product_variant_id.exists' => 'Biến thể sản phẩm không tồn tại.',
            'imei.required' => 'IMEI không được để trống.',
            'imei.digits' => 'IMEI phải gồm đúng 15 chữ số.',
            'imei.unique' => 'IMEI đã tồn tại trong hệ thống.',
            'status.required' => 'Trạng thái không được để trống.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);

        $imei->update([
            'product_variant_id' => $request->product_variant_id,
            'imei' => $request->imei,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('admin.stocks')
            ->with('success', 'Cập nhật IMEI thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $imei = Imei::findOrFail($id);
        $productVariantId = $imei->product_variant_id;

        // Tạo bản ghi lịch sử xuất kho
        InventoryTransaction::create([
            'product_variant_id' => $productVariantId,
            'type' => 'adjustment',
            'quantity' => -1,
            'note' => 'Xóa IMEI: ' . $imei->imei,
        ]);

        $imei->delete();

        return redirect()->route('admin.stocks');
    }

    public function stock(Request $request)
    {
        if ($request->filled('keyword')) {

            $imei = Imei::where('imei', trim($request->keyword))->first();

            if ($imei) {
                return redirect()->route('admin.imeis.show', $imei->id);
            }
        }
        $query = ProductVariant::with([
            'product.brand',
            'imeis'
        ])
        ->whereHas('imeis');

        // tìm kiếm
    if ($request->keyword) {
    
        $keyword = trim($request->keyword);
        

        $query->where(function ($query) use ($keyword) {

            if (preg_match('/^\d{8,15}$/', $keyword)) {

                $query->whereHas('imeis', function ($q) use ($keyword) {
                    $q->where('imei', 'like', "%{$keyword}%");
                });

            } else {

                $query->whereHas('product', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                })
                ->orWhere('color', 'like', "%{$keyword}%")
                ->orWhere('storage', 'like', "%{$keyword}%");

            }

        });

    }

        // lọc brand
        if ($request->brand_id) {

            $query->whereHas('product', function ($q) use ($request) {

                $q->where('brand_id', $request->brand_id);

            });
        }

        $stocks = $query->get()->map(function ($variant) {

            $variant->available_count =
                $variant->imeis
                    ->where('status', 'available')
                    ->count();

            $variant->sold_count =
                $variant->imeis
                    ->where('status', 'sold')
                    ->count();

            $variant->warranty_count =
                $variant->imeis
                    ->where('status', 'warranty')
                    ->count();

            return $variant;
        });

        $brands = \App\Models\Brand::orderBy('name')->get();

        return view(
            'admin.inventory.stocks',
            compact('stocks', 'brands')
        );
    }

    public function accessoryStock(Request $request)
    {
        $query = ProductVariant::with([
            'product.brand',
            'product.category'
        ])->whereHas('product.category', function ($query) {
            $query->where('name', 'like', '%Phụ kiện%');
        });

        if ($request->search) {

            $keyword = trim($request->search);

            $query->where(function ($q) use ($keyword) {

                $q->whereHas('product', function ($productQuery) use ($keyword) {

                    $productQuery->where(
                        'name',
                        'like',
                        "%{$keyword}%"
                    );

                })
                ->orWhere('color', 'like', "%{$keyword}%")
                ->orWhere('storage', 'like', "%{$keyword}%");

            });
        }

        if ($request->brand_id) {

            $query->whereHas('product', function ($q) use ($request) {

                $q->where(
                    'brand_id',
                    $request->brand_id
                );

            });

        }

        $stocks = $query->get();

        $brands = \App\Models\Brand::orderBy('name')->get();

        return view(
            'admin.inventory.accessories',
            compact('stocks', 'brands')
        );
    }
}

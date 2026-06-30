<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryTransaction;
use App\Models\Imei;
use App\Models\ProductVariant;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = InventoryTransaction::with(
            'productVariant.product'
        );

        if ($request->keyword) {
            $keyword = trim($request->keyword);

            $query->where(function ($query) use ($keyword) {
                $query->whereHas('productVariant.product', function ($query) use ($keyword) {
                    $query->where('name', 'like', '%' . $keyword . '%');
                });

                if (is_numeric($keyword)) {
                    $query->orWhere('product_variant_id', $keyword);
                }
            });
        }

        if ($request->transaction_type) {
            $query->where(
                'type',
                $request->transaction_type
            );
        }

        $transactions = $query
            ->latest()
            ->paginate(10);
        $brands = \App\Models\Brand::orderBy('name')->get();

        return view(
            'admin.inventory.index',
            compact('transactions', 'brands')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $quantityVariants = ProductVariant::with('product')
            ->whereHas('product', function ($query) {
                $query->where('product_type', 'quantity');
            })
            ->get();

        return view('admin.inventory.create', compact('quantityVariants'));
    } 

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ], [
            'product_variant_id.required' => 'Bạn phải chọn biến thể phụ kiện.',
            'product_variant_id.exists' => 'Biến thể phụ kiện không tồn tại.',
            'quantity.required' => 'Bạn phải nhập số lượng.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
            'quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1.',
        ]);

        $variant = ProductVariant::findOrFail($request->product_variant_id);

        // Tăng tồn kho
        $variant->increment('stock_quantity', $request->quantity);

        InventoryTransaction::create([
            'product_variant_id' => $request->product_variant_id,
            'type' => 'import',
            'quantity' => $request->quantity,
            'note' => $request->note ?? 'Nhập kho phụ kiện'
        ]);

        return redirect()->route('admin.stocks.accessories')
            ->with('success', 'Đã nhập kho phụ kiện thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $transaction = InventoryTransaction::findOrFail($id);

        return view(
            'admin.inventory.edit',
            compact('transaction')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

            $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);
            
        $transaction =
                InventoryTransaction::findOrFail($id);

            $transaction->update([
                'quantity' => $request->quantity,
                'note' => $request->note
            ]);

            return redirect()->route('admin.inventory.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


}

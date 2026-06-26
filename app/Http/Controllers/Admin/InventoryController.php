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
            $query->where(
                'product_variant_id',
                $request->keyword
            );
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

        return view(
            'admin.inventory.index',
            compact('transactions')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $variants = ProductVariant::with('product')
            ->where('status', true)
            ->get();
        return view('admin.inventory.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);

        InventoryTransaction::create([
            'product_variant_id' => $request->product_variant_id,
            'type' => 'import',
            'quantity' => $request->quantity,
            'note' => $request->note
        ]);

        return redirect()->route('admin.inventory.index');
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

    public function stock(Request $request)
    {
        $query = Imei::with(
            'productVariant.product'
        )
        ->selectRaw(
            'product_variant_id,
            count(*) as total'
        )
        ->where('status', 'available')
        ->groupBy('product_variant_id');

        if($request->variant_id)
        {
            $query->where(
                'product_variant_id',
                $request->variant_id
            );
        }

        $stocks = $query->get();

        return view(
            'admin.inventory.stocks',
            compact('stocks')
        );
    }
}

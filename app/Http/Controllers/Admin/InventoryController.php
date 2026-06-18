<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryTransaction;
use App\Models\Imei;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = InventoryTransaction::latest()->get();

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
        return view('admin.inventory.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         InventoryTransaction::create([
            'product_variant_id' => $request->product_variant_id,
            'type' => 'import',
            'quantity' => $request->quantity,
            'note' => $request->note
        ]);

        return redirect()->route('inventory.index');
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
        $transaction =
                InventoryTransaction::findOrFail($id);

            $transaction->update([
                'quantity' => $request->quantity,
                'note' => $request->note
            ]);

            return redirect()->route('inventory.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function stock()
        {
            $stocks = Imei::selectRaw(
                'product_variant_id,
                count(*) as total'
            )
            ->where('status','available')
            ->groupBy('product_variant_id')
            ->get();

            return view(
                'admin.inventory.stocks',
                compact('stocks')
            );
        }
}

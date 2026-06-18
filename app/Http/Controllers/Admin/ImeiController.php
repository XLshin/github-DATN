<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Imei;
use Illuminate\Validation\Rule;

class ImeiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Imei::query();

        if($request->keyword)
        {
            $query->where(
                'imei',
                'like',
                '%' . $request->keyword . '%'
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
        return view('admin.imeis.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'imei' => 'required|digits:15|unique:imeis,imei'
        ]);

        Imei::create([
            'product_variant_id' => $request->product_variant_id,
            'imei' => $request->imei,
            'status' => 'available'
        ]);

        return redirect()->route('imeis.index');
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
            'imei' => [
                'required',
                Rule::unique('imeis')->ignore($id)
            ]
        ]);

        $imei->update([
            'imei' => $request->imei,
            'status' => $request->status
        ]);

        return redirect()->route('imeis.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Imei::findOrFail($id)->delete();

        return redirect()->route('imeis.index');
    }
}

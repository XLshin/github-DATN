<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {}

    public function index()
    {
        $items = $this->cartService->getItems(auth()->user());
        $total = $this->cartService->calculateTotal($items);

        return view('client.cart.index', compact('items', 'total'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::query()->findOrFail($request->product_id);
        $this->cartService->addItem(auth()->user(), $product, (int) $request->input('quantity', 1));

        return redirect()->back()->with('success', 'Đã thêm vào giỏ hàng.');
    }

    public function remove(Request $request)
    {
        $request->validate(['product_id' => ['required', 'integer']]);

        $this->cartService->removeItem(auth()->user(), (int) $request->product_id);

        return redirect()->route('cart.index')->with('success', 'Đã xóa khỏi giỏ hàng.');
    }
}

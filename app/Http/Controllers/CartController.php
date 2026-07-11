<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $items = $this->cartService->getItems($user);
        $total = $this->cartService->calculateTotal($items);

        return view('client.cart.index', compact('items', 'total'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity'   => 'nullable|integer|min:1',
        ]);

        $product = Product::query()->findOrFail($data['product_id']);
        $quantity = (int) ($data['quantity'] ?? 1);

        $item = $this->cartService->addItem(
            $request->user(),
            $product,
            $quantity,
            $data['variant_id'] ?? null
        );

        $cartCount = $this->cartService->getCartCount($request->user());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng.',
                'cart_count' => $cartCount,
                'item' => [
                    'id' => $item->id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Sản phẩm đã thêm vào giỏ hàng.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'cart_item_id' => 'required|integer',
            'quantity'     => 'required|integer|min:0',
        ]);

        $item = $this->cartService->updateItemQuantity(
            $request->user(),
            (int) $data['cart_item_id'],
            (int) $data['quantity']
        );

        $items = $this->cartService->getItems($request->user());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cart_count' => $this->cartService->getCartCount($request->user()),
                'item_total' => $item ? $this->cartService->unitPrice($item) * $item->quantity : 0,
                'removed' => $item === null,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Đã cập nhật giỏ hàng.');
    }

    public function remove(Request $request)
    {
        $data = $request->validate([
            'cart_item_id' => 'required|integer',
        ]);

        $this->cartService->removeItem($request->user(), (int) $data['cart_item_id']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cart_count' => $this->cartService->getCartCount($request->user()),
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Đã xóa khỏi giỏ hàng.');
    }

    public function count(Request $request)
    {
        return response()->json([
            'cart_count' => $this->cartService->getCartCount($request->user()),
        ]);
    }
}

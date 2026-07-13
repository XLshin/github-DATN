<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
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

        if ($data['variant_id'] ?? null) {
            $variant = ProductVariant::query()->find($data['variant_id']);

            if (! $variant || $variant->product_id !== $product->id) {
                return back()->with('error', 'Biến thể sản phẩm không hợp lệ.');
            }

            if ($variant->stock_quantity < $quantity) {
                return back()->with('error', 'Sản phẩm không đủ tồn kho.');
            }
        }

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

    public function buyNow(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'variant_id' => 'nullable|integer',
            'quantity'   => 'nullable|integer|min:1',
        ]);

        $quantity = (int) $request->input('quantity', 1);
        $product = Product::query()->findOrFail($request->product_id);

        if ($request->filled('variant_id')) {
            $variant = ProductVariant::query()->find($request->variant_id);

            if (! $variant || $variant->product_id !== $product->id) {
                return back()->with('error', 'Biến thể sản phẩm không hợp lệ.');
            }

            if ($variant->stock_quantity < $quantity) {
                return back()->with('error', 'Sản phẩm không đủ tồn kho.');
            }
        }

        $variantId = $request->variant_id ? (int) $request->variant_id : null;
        if (! $variantId) {
            $firstVariant = $product->variants()->where('status', 1)->first();
            $variantId = $firstVariant?->id;
        }

        $this->cartService->addItem(auth()->user(), $product, $quantity, $variantId);

        return redirect()->route('checkout.show');
    }

    public function update(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|integer',
            'quantity'     => 'required|integer|min:1',
        ]);

        $result = $this->cartService->updateItem(
            $request->user(),
            (int) $request->cart_item_id,
            (int) $request->quantity
        );

        if ($request->expectsJson()) {
            if (! $result['success']) {
                return response()->json([
                    'success'      => false,
                    'message'      => $result['message'],
                    'max_quantity' => $result['max_quantity'] ?? null,
                ], 422);
            }

            $items = $this->cartService->getItems($request->user());
            return response()->json([
                'success'    => true,
                'total'      => $this->cartService->calculateTotal($items),
                'cart_count' => $this->cartService->getCartCount($request->user()),
            ]);
        }

        if (! $result['success']) {
            return redirect()->route('cart.index')->with('error', $result['message']);
        }

        return redirect()->route('cart.index')->with('success', 'Đã cập nhật giỏ hàng.');
    }

    public function remove(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|integer',
        ]);

        $this->cartService->removeItem($request->user(), (int) $request->cart_item_id);

        if ($request->expectsJson()) {
            $items = $this->cartService->getItems($request->user());
            return response()->json([
                'success'    => true,
                'total'      => $this->cartService->calculateTotal($items),
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

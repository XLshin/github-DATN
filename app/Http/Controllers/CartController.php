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

    public function index()
    {
        $user  = auth()->user();
        $items = $this->cartService->getItems($user);
        $total = $this->cartService->calculateTotal($items);

        return view('client.cart.index', compact('items', 'total'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'variant_id' => 'nullable|integer',
            'quantity'   => 'nullable|integer|min:1',
        ]);

        $product  = Product::query()->findOrFail($request->product_id);
        $quantity = (int) $request->input('quantity', 1);

        if ($request->filled('variant_id')) {
            $variant = ProductVariant::query()->find($request->variant_id);

            if (! $variant || $variant->product_id !== $product->id) {
                return back()->with('error', 'Biến thể sản phẩm không hợp lệ.');
            }

            if ($variant->stock_quantity < $quantity) {
                return back()->with('error', 'Sản phẩm không đủ tồn kho.');
            }
        }

        // Nếu không chọn variant → tự chọn variant active đầu tiên của sản phẩm
        $variantId = $request->variant_id ? (int) $request->variant_id : null;
        if (! $variantId) {
            $firstVariant = $product->variants()->where('status', 1)->first();
            $variantId = $firstVariant?->id;
        }

        $this->cartService->addItem(auth()->user(), $product, $quantity, $variantId);

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Sản phẩm đã thêm vào giỏ hàng.',
                'cart_count' => $this->cartService->getCount(auth()->user()),
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
            auth()->user(),
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

            $items = $this->cartService->getItems(auth()->user());
            return response()->json([
                'success' => true,
                'total'   => $this->cartService->calculateTotal($items),
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

        $this->cartService->removeItem(auth()->user(), (int) $request->cart_item_id);

        if ($request->expectsJson()) {
            $items = $this->cartService->getItems(auth()->user());
            return response()->json([
                'success' => true,
                'total'   => $this->cartService->calculateTotal($items),
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Đã xóa khỏi giỏ hàng.');
    }
}

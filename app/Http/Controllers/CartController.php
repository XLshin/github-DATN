<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    public function index()
    {
        // Lấy danh sách sản phẩm trong giỏ (truyền user nếu dùng DB, bỏ trống nếu dùng Session)
        $items = method_exists($this->cartService, 'getItems')
            ? $this->cartService->getItems(auth()->user())
            : $this->cartService->all();

        // Tính tổng tiền (nếu Service có hàm calculateTotal)
        $total = method_exists($this->cartService, 'calculateTotal')
            ? $this->cartService->calculateTotal($items)
            : 0;

        // Lưu ý: Đổi tên view thành 'cart.index' hoặc 'client.cart.index' tùy theo cấu trúc thư mục của bạn
        return view('cart.index', compact('items', 'total'));
    }

    public function add(Request $request)
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

        if (method_exists($this->cartService, 'addItem')) {
            $this->cartService->addItem(auth()->user(), $product, $quantity, $request->variant_id);
        } else {
            $this->cartService->add((int) $request->product_id, $request->variant_id ? (int) $request->variant_id : null, $quantity);
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

        $this->cartService->addItem(auth()->user(), $product, $quantity, $request->variant_id);

        return redirect()->route('checkout.show');
    }

    public function update(Request $request)
    {
        $request->validate([
            'key'      => 'required|string',
            'quantity' => 'required|integer|min:0',
        ]);

        if (method_exists($this->cartService, 'update')) {
            $this->cartService->update($request->key, (int) $request->quantity);
        }

        return redirect()->route('cart.index')->with('success', 'Đã cập nhật giỏ hàng.');
    }

    public function remove(Request $request)
    {
        // Validate để nhận cả key (Session) hoặc product_id/cart_item_id (DB)
        $request->validate([
            'key'        => 'nullable|string',
            'product_id' => 'nullable|integer',
            'variant_id' => 'nullable|integer',
        ]);

        if ($request->filled('key') && method_exists($this->cartService, 'remove')) {
            $this->cartService->remove($request->key);
        } elseif ($request->filled('product_id') && method_exists($this->cartService, 'removeItem')) {
            $this->cartService->removeItem(
                auth()->user(),
                (int) $request->product_id,
                $request->filled('variant_id') ? (int) $request->variant_id : null
            );
        }

        return redirect()->route('cart.index')->with('success', 'Đã xóa khỏi giỏ hàng.');
    }
}

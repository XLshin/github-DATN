<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class CartController extends Controller
{
    private CartService $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    public function index()
    {
        $items = $this->cart->all();
        return View::make('cart.index', ['items' => $items]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'variant_id' => 'nullable|integer',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $this->cart->add((int) $request->product_id, $request->variant_id ? (int) $request->variant_id : null, (int) ($request->quantity ?? 1));
        return Redirect::route('cart.index')->with('success', 'Sản phẩm đã thêm vào giỏ hàng.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'quantity' => 'required|integer|min:0',
        ]);

        $this->cart->update($request->key, (int) $request->quantity);
        return Redirect::route('cart.index');
    }

    public function remove(Request $request)
    {
        $request->validate([ 'key' => 'required|string' ]);
        $this->cart->remove($request->key);
        return Redirect::route('cart.index');
    }
}
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

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService,
    ) {}

    public function show()
    {
        $items = $this->cartService->getItems(auth()->user());
        $total = $this->cartService->calculateTotal($items);

        return view('client.checkout.index', compact('items', 'total'));
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'in:cod,card,bank_transfer,momo,vnpay'],
        ]);

        if ($this->cartService->isEmpty(auth()->user())) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống.');
        }

        $order = $this->checkoutService->process(auth()->user(), $validated);

        // For simulated gateways, show a gateway page for redirection
        if (in_array($validated['payment_method'], ['card', 'momo', 'vnpay'])) {
            return view('client.checkout.gateway', ['order' => $order, 'method' => $validated['payment_method']]);
        }

        return redirect()->route('checkout.success', $order->id);
    }

    public function success($orderId)
    {
        $order = Order::with('items')->findOrFail($orderId);

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('client.checkout.success', compact('order'));
    }
}

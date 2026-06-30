<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService,
    ) {}

    public function show(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $items = $this->cartService->getItems($user);
        $total = $this->cartService->calculateTotal($items);

        return view('client.checkout.index', compact('items', 'total'));
    }

    public function process(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'in:cod,card,bank_transfer,momo,vnpay'],
        ]);

        if ($this->cartService->isEmpty($user)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Giỏ hàng trống.');
        }

        try {
            $order = $this->checkoutService->process($user, $validated);
        } catch (ValidationException $e) {
            return redirect()
                ->route('cart.index')
                ->withErrors($e->errors())
                ->with('error', collect($e->errors())->flatten()->first());
        }

        if (in_array($validated['payment_method'], ['card', 'momo', 'vnpay'], true)) {
            return view('client.checkout.gateway', [
                'order' => $order,
                'method' => $validated['payment_method'],
            ]);
        }

        return redirect()->route('checkout.success', $order->getKey());
    }

    public function success(Request $request, int|string $orderId)
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $order = Order::with('items')->findOrFail($orderId);

        if ((int) $order->user_id !== (int) $user->getKey()) {
            abort(403);
        }

        return view('client.checkout.success', compact('order'));
    }
}
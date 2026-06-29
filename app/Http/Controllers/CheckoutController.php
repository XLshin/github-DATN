<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'points_to_use' => ['nullable', 'integer', 'min:0'],
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

    public function preview(Request $request)
    {
        $data = $request->validate([
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'points_to_use' => ['nullable', 'integer', 'min:0'],
        ]);

        $user = auth()->user();
        $items = $this->cartService->getItems($user);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Giỏ hàng trống.']);
        }

        $subtotal = $this->cartService->calculateTotal($items);

        $coupon = null;
        $couponDiscount = 0;
        if (!empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper($data['coupon_code']))->first();
            if (! $coupon || ! $coupon->isValidForAmount($subtotal)) {
                throw ValidationException::withMessages(['coupon_code' => 'Mã voucher không hợp lệ hoặc không đáp ứng điều kiện.']);
            }
            $couponDiscount = $coupon->discountAmount($subtotal);
        }

        $pointsToUse = (int) ($data['points_to_use'] ?? 0);
        $pointsDiscount = 0;
        if ($pointsToUse > 0) {
            if ($user->points < $pointsToUse) {
                throw ValidationException::withMessages(['points_to_use' => 'Bạn không có đủ điểm để đổi.']);
            }
            $maxRedeemable = (int) floor(max($subtotal - $couponDiscount, 0));
            $pointsToUse = min($pointsToUse, $maxRedeemable);
            $pointsDiscount = $pointsToUse; // 1 point = 1 đ
        }

        $totalAfter = max($subtotal - $couponDiscount - $pointsDiscount, 0);

        return response()->json([
            'subtotal' => $subtotal,
            'coupon_discount' => $couponDiscount,
            'points_used' => $pointsToUse,
            'points_discount' => $pointsDiscount,
            'total' => $totalAfter,
        ]);
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

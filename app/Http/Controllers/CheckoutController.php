<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
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

    public function show(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $items = $this->cartService->getItems($user);
        $total = $this->cartService->calculateTotal($items);

        // Load danh sách voucher được cấp cho user này
        $availableCoupons = $user->coupons()
            ->where('status', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();

        return view('client.checkout.index', compact('items', 'total', 'availableCoupons'));
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
            'coupon_id' => ['nullable', 'integer', 'exists:coupons,id'],
            'points_to_use' => ['nullable', 'integer', 'min:0'],
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

    public function preview(Request $request)
    {
        $data = $request->validate([
            'coupon_id' => ['nullable', 'integer', 'exists:coupons,id'],
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
        if (!empty($data['coupon_id'])) {
            $coupon = Coupon::findOrFail($data['coupon_id']);
            // Kiểm tra user có quyền dùng coupon này không
            if (!$user->coupons->contains($coupon->id)) {
                throw ValidationException::withMessages(['coupon_id' => 'Voucher không hợp lệ hoặc bạn không có quyền sử dụng.']);
            }
            if (! $coupon->isValidForAmount($subtotal)) {
                throw ValidationException::withMessages(['coupon_id' => 'Mã voucher không đáp ứng điều kiện tối thiểu.']);
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

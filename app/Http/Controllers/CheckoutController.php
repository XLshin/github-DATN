<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    private const SELECTED_ITEMS_SESSION_KEY = 'checkout_selected_items';

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

        $itemIds = $this->resolveSelectedItemIds($request);
        $items = $this->cartService->getItems($user, $itemIds);

        if ($items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.');
        }

        // Ghi nhớ đúng các dòng đã chọn để dùng lại ở preview()/process() (không có trong query string).
        session([self::SELECTED_ITEMS_SESSION_KEY => $items->pluck('id')->all()]);

        $total = $this->cartService->calculateTotal($items);

        // Load danh sách voucher được cấp cho user này
        $availableCoupons = $user->coupons()
            ->where('status', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();

        return view('client.checkout.index', compact('items', 'total', 'availableCoupons'));
    }

    /**
     * Lấy danh sách item id đã chọn từ query string ?items=1,2,3 (khi vào từ giỏ hàng)
     * hoặc từ session (khi tải lại trang checkout).
     *
     * @return array<int, int>|null null nghĩa là không giới hạn (toàn bộ giỏ hàng).
     */
    private function resolveSelectedItemIds(Request $request): ?array
    {
        if ($request->filled('items')) {
            $ids = array_values(array_filter(array_map('intval', explode(',', (string) $request->query('items')))));

            return empty($ids) ? null : $ids;
        }

        $sessionIds = session(self::SELECTED_ITEMS_SESSION_KEY);

        return (is_array($sessionIds) && ! empty($sessionIds)) ? $sessionIds : null;
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
            'buyer_type' => ['required', 'string', 'in:self,proxy'],
            'buyer_name' => ['required_if:buyer_type,proxy', 'nullable', 'string', 'max:255'],
            'buyer_phone' => ['required_if:buyer_type,proxy', 'nullable', 'string', 'max:20'],
            'payment_method' => ['required', 'string', 'in:cod,card,bank_transfer,momo,vnpay'],
            'coupon_id' => ['nullable', 'integer', 'exists:coupons,id'],
            'points_to_use' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($this->cartService->isEmpty($user)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Giỏ hàng trống.');
        }

        $sessionIds = session(self::SELECTED_ITEMS_SESSION_KEY);
        $itemIds = (is_array($sessionIds) && ! empty($sessionIds)) ? $sessionIds : null;

        try {
            $order = $this->checkoutService->process($user, $validated, $itemIds);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return redirect()
                ->route('cart.index')
                ->with('error', $firstError);
        }

        session()->forget(self::SELECTED_ITEMS_SESSION_KEY);

        // Route đến trang thanh toán phù hợp với phương thức
        return match ($validated['payment_method']) {
            'cod'           => redirect()->route('checkout.success', $order)
                ->with('success', 'Đặt hàng thành công! Thanh toán khi nhận hàng.'),
            'bank_transfer' => redirect()->route('checkout.payment', $order),
            'momo'          => redirect()->route('checkout.payment', $order),
            'vnpay'         => redirect()->route('checkout.payment', $order),
            'card'          => redirect()->route('checkout.payment', $order),
            default         => redirect()->route('checkout.success', $order),
        };
    }

    /**
     * Trang thanh toán theo phương thức (bank_transfer / momo / vnpay / card).
     */
    public function showPayment(Request $request, Order $order)
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user || (int) $order->user_id !== (int) $user->getKey()) {
            abort(403);
        }

        $payment = $order->payment;

        if ($payment?->payment_status === 'paid') {
            return redirect()->route('checkout.success', $order);
        }

        if ($payment?->isExpired()) {
            $this->checkoutService->expirePayment($payment);
            $payment->refresh();
        }

        return view('client.checkout.payment', compact('order'));
    }

    /**
     * Thử lại giao dịch đã hết hạn: cấp lại tồn kho/IMEI và mở phiên thanh toán mới.
     */
    public function retryPayment(Request $request, Order $order)
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user || (int) $order->user_id !== (int) $user->getKey()) {
            abort(403);
        }

        $payment = $order->payment;

        if (! $payment || $payment->payment_status !== 'failed') {
            return redirect()->route('checkout.payment', $order);
        }

        try {
            $this->checkoutService->retryPayment($payment);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return redirect()->route('checkout.success', $order)->with('error', $firstError);
        }

        return redirect()->route('checkout.payment', $order)
            ->with('info', 'Đã mở lại phiên thanh toán mới, vui lòng hoàn tất trong thời gian quy định.');
    }

    /**
     * Xác nhận thanh toán (momo / vnpay / card / bank_transfer).
     */
    public function confirmPayment(Request $request, Order $order)
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user || (int) $order->user_id !== (int) $user->getKey()) {
            abort(403);
        }

        $payment = $order->payment;
        if (! $payment || $payment->payment_status === 'paid') {
            return redirect()->route('checkout.success', $order);
        }

        if ($payment->isExpired()) {
            $this->checkoutService->expirePayment($payment);
            return redirect()->route('checkout.payment', $order)
                ->with('error', 'Giao dịch đã hết hạn do quá thời gian thanh toán. Vui lòng thử lại.');
        }

        $method = $payment->payment_method;

        if ($method === 'bank_transfer') {
            // Khách xác nhận đã chuyển khoản: ghi nhận, chờ nhân viên đối soát thủ công
            $payment->update([
                'payment_status' => 'pending',
                'payer_name'     => $order->customer_name,
                'payer_note'     => 'Khách báo đã chuyển khoản lúc ' . now()->format('H:i d/m/Y') . ' — chờ đối soát.',
            ]);
            return redirect()->route('checkout.success', $order)
                ->with('info', 'Chúng tôi đã ghi nhận. Đơn sẽ được xác nhận sau khi xác minh chuyển khoản (thường trong 30 phút).');
        }

        if ($method === 'card') {
            $validated = $request->validate([
                'card_number' => ['required', 'string'],
                'card_expiry' => ['required', 'string', 'regex:/^\d{2}\/\d{2}$/'],
                'card_cvv'    => ['required', 'string', 'min:3', 'max:4'],
                'card_name'   => ['required', 'string', 'max:255'],
            ]);

            $digits = preg_replace('/\D/', '', $validated['card_number']);

            if (! $this->isValidCardNumber($digits)) {
                throw ValidationException::withMessages([
                    'card_number' => 'Số thẻ không hợp lệ. Vui lòng kiểm tra lại.',
                ]);
            }

            $expMonth = (int) substr($validated['card_expiry'], 0, 2);
            $expYear  = 2000 + (int) substr($validated['card_expiry'], 3, 2);
            $now      = now();
            $expired  = $expYear < $now->year || ($expYear === $now->year && $expMonth < $now->month);

            if ($expMonth < 1 || $expMonth > 12 || $expired) {
                throw ValidationException::withMessages([
                    'card_expiry' => 'Thẻ đã hết hạn hoặc ngày hết hạn không hợp lệ.',
                ]);
            }

            // Mô phỏng ngân hàng phát hành từ chối giao dịch với thẻ test kết thúc bằng 0000
            if (str_ends_with($digits, '0000')) {
                throw ValidationException::withMessages([
                    'card_number' => 'Giao dịch bị từ chối bởi ngân hàng phát hành thẻ (số dư không đủ).',
                ]);
            }

            $payment->update([
                'payment_status'   => 'paid',
                'transaction_code' => 'CARD' . strtoupper(Str::random(10)),
                'payer_name'       => Str::upper($validated['card_name']),
                'payer_note'       => '**** **** **** ' . substr($digits, -4),
                'paid_at'          => now(),
            ]);
            $order->update(['status' => 'confirmed']);

            return redirect()->route('checkout.success', $order)
                ->with('success', 'Thanh toán thành công!');
        }

        // Momo / VNPay: xác nhận từ trang giả lập ví/cổng thanh toán → đơn confirmed
        $payment->update([
            'payment_status'   => 'paid',
            'transaction_code' => strtoupper($method) . strtoupper(Str::random(10)),
            'payer_name'       => $order->customer_name,
            'payer_note'       => $method === 'momo'
                ? 'Ví MoMo liên kết SĐT ' . $order->customer_phone
                : 'Tài khoản ngân hàng liên kết VNPAY',
            'paid_at'          => now(),
        ]);
        $order->update(['status' => 'confirmed']);

        return redirect()->route('checkout.success', $order)
            ->with('success', 'Thanh toán thành công!');
    }

    /**
     * Kiểm tra số thẻ hợp lệ theo thuật toán Luhn (chuẩn dùng bởi Visa/Mastercard/JCB).
     */
    private function isValidCardNumber(string $digits): bool
    {
        if (! ctype_digit($digits) || strlen($digits) < 13 || strlen($digits) > 19) {
            return false;
        }

        $sum = 0;
        $alternate = false;

        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $n = (int) $digits[$i];

            if ($alternate) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }

            $sum += $n;
            $alternate = ! $alternate;
        }

        return $sum % 10 === 0;
    }

    public function preview(Request $request)
    {
        $data = $request->validate([
            'coupon_id' => ['nullable', 'integer', 'exists:coupons,id'],
            'points_to_use' => ['nullable', 'integer', 'min:0'],
        ]);

        $user = auth()->user();
        $sessionIds = session(self::SELECTED_ITEMS_SESSION_KEY);
        $itemIds = (is_array($sessionIds) && ! empty($sessionIds)) ? $sessionIds : null;
        $items = $this->cartService->getItems($user, $itemIds);

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

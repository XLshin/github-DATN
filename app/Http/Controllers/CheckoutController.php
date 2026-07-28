<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\BankTransactionLogService;
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
        private readonly BankTransactionLogService $logService,
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
        $selectedIds = $itemIds ?? [];

        if ($items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.');
        }

        // Ghi nhớ đúng các dòng đã chọn để dùng lại ở preview()/process() (không có trong query string).
        session([self::SELECTED_ITEMS_SESSION_KEY => $items->pluck('id')->all()]);

        $total = $this->cartService->calculateTotal($items);

        // Load danh sách voucher được cấp cho user này
        $availableCoupons = $user->coupons()->valid()->get();

        // Địa chỉ đã lưu của khách, mặc định lên đầu để chọn sẵn
        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return view('client.checkout.index', compact('items', 'total', 'availableCoupons', 'selectedIds', 'addresses'));
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
            'payment_method' => ['required', 'string', 'in:cod,bank_transfer,vietqr,wallet'],
            'coupon_id' => ['nullable', 'integer', 'exists:coupons,id'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'points_to_use' => ['nullable', 'integer', 'min:0'],
            'cart_item_ids' => ['nullable', 'array'],
            'cart_item_ids.*' => ['integer'],
            'buyer_type' => ['nullable', 'string', 'in:self,proxy'],
            'buyer_name' => ['nullable', 'required_if:buyer_type,proxy', 'string', 'max:255'],
            'buyer_phone' => ['nullable', 'required_if:buyer_type,proxy', 'string', 'max:20'],
        ]);

        $sessionIds = session(self::SELECTED_ITEMS_SESSION_KEY);
        $itemIds = (is_array($sessionIds) && ! empty($sessionIds)) ? $sessionIds : null;
        $items = $this->cartService->getItems($user, $itemIds);

        if ($items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.');
        }

        // if user provided a coupon code prefer it over selected coupon_id
        if (! empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', Str::upper(trim($validated['coupon_code'])))->first();
            if (! $coupon) {
                return back()->withErrors(['coupon_code' => 'Mã voucher không tồn tại.']);
            }
            // if coupon is restricted to specific users, ensure current user is allowed
            if (! $user->coupons()->whereKey($coupon->id)->exists()) {
                return back()->withErrors(['coupon_code' => 'Bạn không có quyền sử dụng mã voucher này.']);
            }
            if (! $coupon->isValidForAmount($this->cartService->calculateTotal($this->cartService->getItems($user)))) {
                return back()->withErrors(['coupon_code' => 'Mã voucher không hợp lệ hoặc không đáp ứng điều kiện.']);
            }
            $validated['coupon_id'] = $coupon->id;
        }

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
            'wallet'        => redirect()->route('checkout.success', $order)
                ->with('success', 'Đặt hàng thành công! Đã thanh toán bằng số dư ví.'),
            'bank_transfer' => redirect()->route('checkout.payment', $order),
            'vietqr'        => redirect()->route('checkout.payment', $order),
            default         => redirect()->route('checkout.success', $order),
        };
    }

    /**
     * Trang thanh toán theo phương thức (bank_transfer / vietqr).
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

    
    public function paymentStatus(Request $request, Order $order, \App\Services\PaymentWebhookService $webhookService)
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user || (int) $order->user_id !== (int) $user->getKey()) {
            abort(403);
        }

        $payment = $order->payment;

        if (
            $payment
            && $payment->payment_status === 'pending'
            && in_array($payment->payment_method, CheckoutService::AUTO_CONFIRM_METHODS, true)
            && $payment->simulate_confirm_at
            && $payment->simulate_confirm_at->isPast()
            && ! $payment->isExpired()
        ) {
            $webhookService->confirmSimulatedBankTransfer($payment);
            $payment->refresh();
        }

        return response()->json([
            'status' => $payment?->payment_status,
            'paid'   => $payment?->payment_status === 'paid',
            'receipt' => $payment?->payment_status === 'paid' ? [
                'payer_name'       => $payment->payer_name,
                'amount'           => (float) $payment->amount,
                'transaction_code' => $payment->transaction_code,
                'paid_at'          => $payment->paid_at?->format('H:i:s d/m/Y'),
                'business_account' => [
                    'bank_id'        => config('services.sepay.bank_id'),
                    'account_number' => config('services.sepay.account_number'),
                    'account_name'   => config('services.sepay.account_name'),
                ],
            ] : null,
        ]);
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
     * Khách xác nhận thanh toán thủ công — dùng làm lối dự phòng khi việc tự động xác nhận
     * (simulate_confirm_at, xem paymentStatus()) chưa kịp chạy. vietqr/bank_transfer chỉ có ảnh
     * chụp màn hình nên vẫn cần admin đối soát.
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

        $validated = $request->validate([
            'proof_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ], [
            'proof_image.required' => 'Vui lòng tải lên ảnh chụp màn hình xác nhận giao dịch.',
            'proof_image.image' => 'File tải lên phải là hình ảnh.',
        ]);

        $payerNote = match ($method) {
            'bank_transfer' => 'Khách báo đã chuyển khoản lúc ' . now()->format('H:i d/m/Y') . ' — chờ đối soát.',
            'vietqr' => 'Khách báo đã thanh toán qua VietQR — chờ đối soát.',
            default => 'Khách báo đã thanh toán — chờ đối soát.',
        };

        $path = $request->file('proof_image')->store('order-payment-proofs', 'public');

        $payment->update([
            'payment_status' => 'pending',
            'payer_name'     => $order->customer_name,
            'payer_note'     => $payerNote,
            'proof_image'    => $path,
        ]);

        $this->logService->logOrderPayment($payment->fresh(), 'pending', null, 'Khách gửi bằng chứng thanh toán, chờ đối soát.');

        return redirect()->route('checkout.success', $order)
            ->with('info', 'Chúng tôi đã ghi nhận yêu cầu thanh toán. Đơn sẽ được xác nhận sau khi đối soát (thường trong 30 phút).');
    }

    public function preview(Request $request)
    {
        $data = $request->validate([
            'coupon_id' => ['nullable', 'integer', 'exists:coupons,id'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'points_to_use' => ['nullable', 'integer', 'min:0'],
            'cart_item_ids' => ['nullable', 'array'],
            'cart_item_ids.*' => ['integer'],
        ]);

        // enforce mutual exclusivity in preview as well
        if (!empty($data['coupon_code']) && !empty($data['coupon_id'])) {
            throw ValidationException::withMessages(['coupon_id' => 'Chỉ được dùng một loại voucher: nhập mã hoặc chọn voucher sẵn có.']);
        }

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
        // resolve coupon either by selected id or entered code
        if (!empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', Str::upper(trim($data['coupon_code'])))->first();
            if (! $coupon) {
                throw ValidationException::withMessages(['coupon_code' => 'Mã voucher không tồn tại.']);
            }
            if (! $user->coupons()->whereKey($coupon->id)->exists()) {
                throw ValidationException::withMessages(['coupon_code' => 'Bạn không có quyền sử dụng mã voucher này.']);
            }
            if (! $coupon->isValidForAmount($subtotal)) {
                throw ValidationException::withMessages(['coupon_code' => 'Mã voucher không đáp ứng điều kiện tối thiểu.']);
            }
            $couponDiscount = $coupon->discountAmount($subtotal);
        } elseif (!empty($data['coupon_id'])) {
            $coupon = Coupon::findOrFail($data['coupon_id']);
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

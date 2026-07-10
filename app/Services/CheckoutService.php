<?php

namespace App\Services;


use App\Models\Coupon;
use App\Models\Imei;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /** Phương thức thanh toán qua cổng có phiên giao dịch giới hạn thời gian, giống thực tế (QR/thẻ hết hạn). */
    private const EXPIRING_METHODS = ['card', 'momo', 'vnpay'];

    private const PAYMENT_EXPIRY_MINUTES = 15;

    public function __construct(
        private readonly CartService $cartService,
        private readonly PointService $pointService,
    ) {}

    /**
     * @param  array<int, int>|null  $itemIds  Chỉ thanh toán đúng các dòng giỏ hàng này (null = toàn bộ giỏ).
     */
    public function process(User $user, array $data, ?array $itemIds = null): Order
    {
        $items = $this->cartService->getItems($user, $itemIds);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Giỏ hàng trống.',
            ]);
        }

        return DB::transaction(function () use ($user, $data, $items, $itemIds) {
            $subtotal = $this->cartService->calculateTotal($items);
            $coupon = null;
            $couponDiscount = 0;

            if (! empty($data['coupon_id'])) {
                $coupon = Coupon::findOrFail($data['coupon_id']);

                // Verify user has access to this coupon
                if (!$user->coupons->contains($coupon->id)) {
                    throw ValidationException::withMessages([
                        'coupon_id' => 'Voucher không hợp lệ hoặc bạn không có quyền sử dụng.',
                    ]);
                }

                if (! $coupon->isValidForAmount($subtotal)) {
                    throw ValidationException::withMessages([
                        'coupon_id' => 'Mã voucher không đáp ứng điều kiện tối thiểu.',
                    ]);
                }

                $couponDiscount = $coupon->discountAmount($subtotal);
            }

            $order = Order::query()->create([
                'user_id' => $user->id,
                'order_code' => $this->generateOrderCode(),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'shipping_address' => $data['shipping_address'],
                'buyer_type' => $data['buyer_type'] ?? 'self',
                'buyer_name' => ($data['buyer_type'] ?? 'self') === 'proxy' ? ($data['buyer_name'] ?? null) : null,
                'buyer_phone' => ($data['buyer_type'] ?? 'self') === 'proxy' ? ($data['buyer_phone'] ?? null) : null,
                'subtotal' => $subtotal,
                'membership_discount' => 0,
                'coupon_discount' => $couponDiscount,
                'points_used' => 0,
                'points_discount' => 0,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'total_amount' => max($subtotal - $couponDiscount, 0),
                'status' => 'pending',
                'fulfillment_status' => 'pending',
            ]);

            foreach ($items as $item) {
                $product = $item->product;
                $variant = $item->productVariant;

                if (! $product) {
                    throw ValidationException::withMessages([
                        'product' => 'Có sản phẩm trong giỏ hàng không còn tồn tại.',
                    ]);
                }

                if (! $variant) {
                    throw ValidationException::withMessages([
                        'variant' => 'Có biến thể sản phẩm trong giỏ hàng không còn tồn tại.',
                    ]);
                }

                $quantity = (int) $item->quantity;
                $price = (float) $product->price;
                $productType = $product->product_type;

                if ($productType === 'imei/serial') {
                    // Mỗi đơn vị điện thoại gắn với đúng 1 IMEI riêng biệt
                    for ($i = 0; $i < $quantity; $i++) {
                        $orderItem = OrderItem::query()->create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $variant->id,
                            'price' => $price,
                            'quantity' => 1,
                            'total' => $price,
                            'imei_id' => null,
                        ]);

                        $this->reserveImeiForOrderItem($orderItem, $variant->id, $product->name);
                    }
                } else {
                    $orderItem = OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'price' => $price,
                        'quantity' => $quantity,
                        'total' => $price * $quantity,
                        'imei_id' => null,
                    ]);

                    $this->decreaseVariantStock($variant, $quantity, $product->name);
                }

                InventoryTransaction::query()->create([
                    'product_variant_id' => $variant->id,
                    'type' => 'export',
                    'quantity' => $quantity,
                    'note' => 'Tạm giữ / xuất kho cho đơn hàng: ' . $order->order_code,
                ]);
            }

            Payment::query()->create([
                'order_id'         => $order->id,
                'payment_method'   => $data['payment_method'],
                'amount'           => $order->total_amount,
                'payment_status'   => 'pending',   // Luôn pending, xác nhận sau qua trang thanh toán
                'transaction_code' => null,
                'paid_at'          => null,
                'expires_at'       => in_array($data['payment_method'], self::EXPIRING_METHODS, true)
                    ? now()->addMinutes(self::PAYMENT_EXPIRY_MINUTES)
                    : null,
            ]);

            $pointsEarned = $this->pointService->calculatePointsFromOrder($order->total_amount);

            if ($pointsEarned > 0) {
                $this->pointService->addPoints(
                    $user,
                    $pointsEarned,
                    'purchase',
                    "Mua hàng - Đơn hàng #{$order->order_code}"
                );
            }
            if ($itemIds === null) {
                $this->cartService->clear($user);
            } else {
                $this->cartService->removeItems($user, $itemIds);
            }

            return $order;
        });
    }

    private function reserveImeiForOrderItem(OrderItem $orderItem, int $productVariantId, string $productName): void
    {
        $imei = Imei::query()
            ->where('product_variant_id', $productVariantId)
            ->where('status', 'available')
            ->lockForUpdate()
            ->first();

        if (! $imei) {
            throw ValidationException::withMessages([
                'inventory' => 'Sản phẩm ' . $productName . ' đã hết IMEI khả dụng.',
            ]);
        }

        $imei->update([
            'status' => 'reserved',
            'reserved_at' => now(),
            'reserved_by_order_item_id' => $orderItem->id,
        ]);

        $orderItem->update([
            'imei_id' => $imei->id,
        ]);
    }

    private function decreaseVariantStock($variant, int $quantity, string $productName): void
    {
        $freshVariant = $variant->newQuery()
            ->whereKey($variant->id)
            ->lockForUpdate()
            ->first();

        if (! $freshVariant || $freshVariant->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'inventory' => 'Sản phẩm ' . $productName . ' không đủ tồn kho.',
            ]);
        }

        $freshVariant->decrement('stock_quantity', $quantity);
    }

    /**
     * Đánh dấu giao dịch hết hạn (quá thời gian giữ chỗ) và hoàn lại tồn kho/IMEI đã tạm giữ.
     */
    public function expirePayment(Payment $payment): void
    {
        if (! $payment->isExpired()) {
            return;
        }

        DB::transaction(function () use ($payment) {
            $this->restoreInventory($payment->order);

            $payment->update([
                'payment_status' => 'failed',
                'payer_note'     => 'Giao dịch hết hạn do quá thời gian thanh toán.',
            ]);
        });
    }

    /**
     * Thử thanh toán lại: cấp lại tồn kho/IMEI và mở phiên giao dịch mới (transaction_code + hạn mới).
     *
     * @throws ValidationException nếu không còn đủ tồn kho/IMEI để giữ chỗ lại
     */
    public function retryPayment(Payment $payment): void
    {
        if ($payment->payment_status === 'paid' || ! in_array($payment->payment_method, self::EXPIRING_METHODS, true)) {
            return;
        }

        DB::transaction(function () use ($payment) {
            $this->reallocateInventory($payment->order);

            $payment->update([
                'payment_status'   => 'pending',
                'transaction_code' => null,
                'payer_name'       => null,
                'payer_note'       => null,
                'expires_at'       => now()->addMinutes(self::PAYMENT_EXPIRY_MINUTES),
            ]);
        });
    }

    /**
     * Hoàn lại tồn kho/IMEI đã tạm giữ cho 1 đơn hàng (khi giao dịch hết hạn/thất bại), không hủy đơn.
     */
    private function restoreInventory(Order $order): void
    {
        foreach ($order->items()->with(['product', 'variant'])->get() as $item) {
            if ($item->imei_id) {
                $imei = Imei::find($item->imei_id);

                if ($imei && $imei->status === 'reserved') {
                    $imei->update([
                        'status'                    => 'available',
                        'reserved_at'                => null,
                        'reserved_by_order_item_id' => null,
                    ]);
                }

                $item->update(['imei_id' => null]);
            } elseif ($item->product?->product_type === 'quantity' && $item->variant) {
                $item->variant->increment('stock_quantity', $item->quantity);
            }
        }
    }

    /**
     * Ngược lại với restoreInventory(): giữ chỗ lại tồn kho/IMEI cho đơn đã có sẵn (dùng khi thử lại thanh toán).
     */
    private function reallocateInventory(Order $order): void
    {
        foreach ($order->items()->with(['product', 'variant'])->get() as $item) {
            $product = $item->product;

            if (! $product) {
                continue;
            }

            if ($product->product_type === 'imei/serial') {
                $this->reserveImeiForOrderItem($item, $item->product_variant_id, $product->name);
            } elseif ($item->variant) {
                $this->decreaseVariantStock($item->variant, $item->quantity, $product->name);
            }
        }
    }

    private function generateOrderCode(): string
    {
        do {
            $code = 'ORD' . now()->format('YmdHis') . strtoupper(Str::random(4));
        } while (Order::query()->where('order_code', $code)->exists());

        return $code;
    }
}

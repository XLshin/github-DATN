<?php

namespace App\Services;


use App\Models\Coupon;
use App\Models\Imei;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReceiver;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
    private readonly CartService $cartService,
    private readonly PointService $pointService,
) {}

    public function process(User $user, array $data, ?Collection $items = null): Order
    {
        $items ??= $this->cartService->getItems($user);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Giỏ hàng trống.',
            ]);
        }

        return DB::transaction(function () use ($user, $data, $items) {
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

            $buyerType = $data['buyer_type'] ?? 'self';

            $order = Order::query()->create([
                'user_id' => $user->id,
                'buyer_type' => $buyerType,
                'buyer_name' => $buyerType === 'proxy' ? $data['buyer_name'] : null,
                'buyer_phone' => $buyerType === 'proxy' ? $data['buyer_phone'] : null,
                'order_code' => $this->generateOrderCode(),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'shipping_address' => $data['shipping_address'],
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

            OrderReceiver::query()->create([
                'order_id' => $order->id,
                'receiver_name' => $data['customer_name'],
                'receiver_phone' => $data['customer_phone'],
                'receiver_address' => $data['shipping_address'],
                'receiver_note' => $buyerType === 'proxy'
                    ? 'Đặt hộ bởi: ' . $data['buyer_name'] . ' (' . $data['buyer_phone'] . ')'
                    : null,
            ]);

            foreach ($items as $item) {
                $product = $item->product;

                // Cập nhật kho dựa trên loại sản phẩm
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
                $price = $this->cartService->unitPrice($item);
                $productType = $product->product_type;

                if ($productType === 'imei/serial' && $quantity > 1) {
                    throw ValidationException::withMessages([
                        'inventory' => 'Sản phẩm điện thoại chỉ được đặt số lượng 1 cho mỗi dòng sản phẩm.',
                    ]);
                }

                $orderItem = OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $price * $quantity,
                    'imei_id' => null,
                ]);

                if ($productType === 'imei/serial') {
                    $this->assertImeiAvailable($variant->id, $product->name);
                }

                if ($productType === 'quantity') {
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
                'order_id' => $order->id,
                'payment_method' => $data['payment_method'],
                'amount' => $order->total_amount,
                'payment_status' => $data['payment_method'] === 'cod' ? 'pending' : 'paid',
                'transaction_code' => null,
                'paid_at' => $data['payment_method'] === 'cod' ? null : now(),
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
            $this->cartService->clearItems($user, $items->pluck('id')->all());

            return $order;
        });
    }

    /**
     * Chỉ kiểm tra còn IMEI khả dụng cho biến thể, không gán/giữ chỗ một IMEI cụ thể.
     * Việc chọn IMEI nào cụ thể sẽ do admin thực hiện thủ công ở bước xác nhận đóng gói.
     */
    private function assertImeiAvailable(int $productVariantId, string $productName): void
    {
        $exists = Imei::query()
            ->where('product_variant_id', $productVariantId)
            ->where('status', 'available')
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'inventory' => 'Sản phẩm ' . $productName . ' đã hết IMEI khả dụng.',
            ]);
        }
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

    private function generateOrderCode(): string
    {
        do {
            $code = 'ORD' . now()->format('YmdHis') . strtoupper(Str::random(4));
        } while (Order::query()->where('order_code', $code)->exists());

        return $code;
    }
}

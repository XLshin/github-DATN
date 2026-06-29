<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    private CartService $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    public function process($user, array $data): Order
    {
        $items = $this->cart->getItems($user);
        $total = $this->cart->calculateTotal($items);

        return DB::transaction(function () use ($user, $data, $items, $total) {
            $order = Order::create([
                'user_id' => $user->id,
                'order_code' => 'ORD_'.time(),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'shipping_address' => $data['shipping_address'],
                'subtotal' => $total,
                'membership_discount' => 0,
                'coupon_discount' => 0,
                'total_amount' => $total,
                'status' => $data['payment_method'] === 'cod' ? 'processing' : 'pending',
            ]);

            foreach ($items as $it) {
                $order->items()->create([
                    'product_id' => $it['product_id'],
                    'product_variant_id' => $it['variant_id'] ?? null,
                    'price' => $it['price'] ?? 0,
                    'quantity' => $it['quantity'] ?? 1,
                    'total' => ($it['price'] ?? 0) * ($it['quantity'] ?? 1),
                ]);
            }

            // create a payment record placeholder
            $order->payment()->create([
                'payment_method' => $data['payment_method'] ?? 'cod',
                'amount' => $total,
                'payment_status' => $data['payment_method'] === 'cod' ? 'pending' : 'pending',
            ]);

            // clear cart
            $this->cart->clear();

            return $order;
        });
    }
}
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Models\Imei;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly \App\Services\ImeiReservationService $imeiReservationService,
        private readonly PointService $pointService,
    ) {}

    public function process(User $user, array $data): Order
    {
        $items = $this->cartService->getItems($user);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Giỏ hàng trống.',
            ]);
        }

        return DB::transaction(function () use ($user, $data, $items) {
            $subtotal = $this->cartService->calculateTotal($items);
            $coupon = null;
            $couponDiscount = 0;

            if (! empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', strtoupper($data['coupon_code']))->first();

                if (! $coupon || ! $coupon->isValidForAmount($subtotal)) {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'Mã voucher không hợp lệ hoặc không đáp ứng điều kiện.',
                    ]);
                }

                $couponDiscount = $coupon->discountAmount($subtotal);
            }

            $order = Order::query()->create([
                'user_id' => $user->id,
                'order_code' => strtoupper(Str::random(10)),
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
            ]);

            foreach ($items as $item) {
                $price = (float) $item->product->price;

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'price' => $price,
                    'quantity' => $item->quantity,
                    'total' => $price * $item->quantity,
                ]);

                // Cập nhật kho dựa trên loại sản phẩm
                $variant = $item->productVariant;
                $hasImei = $variant->imeis()->exists();

                if ($hasImei) {
                    // Điện thoại: cập nhật IMEI status thành 'sold'
                    Imei::where('product_variant_id', $item->product_variant_id)
                        ->where('status', 'available')
                        ->limit($item->quantity)
                        ->update(['status' => 'sold']);

                    // Tạo transaction
                    InventoryTransaction::create([
                        'product_variant_id' => $item->product_variant_id,
                        'type' => 'export',
                        'quantity' => $item->quantity,
                        'note' => 'Bán hàng - Đơn: ' . $order->order_code,
                    ]);
                } else {
                    // Phụ kiện: giảm stock_quantity
                    $variant->decrement('stock_quantity', $item->quantity);

                    // Tạo transaction
                    InventoryTransaction::create([
                        'product_variant_id' => $item->product_variant_id,
                        'type' => 'export',
                        'quantity' => $item->quantity,
                        'note' => 'Bán hàng - Đơn: ' . $order->order_code,
                    ]);
                }
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            // Handle points redemption if provided
            $pointsToUse = (int) ($data['points_to_use'] ?? 0);
            if ($pointsToUse > 0) {
                // Validate user has enough points
                if ($user->points < $pointsToUse) {
                    throw ValidationException::withMessages([
                        'points_to_use' => 'Bạn không có đủ điểm để đổi.',
                    ]);
                }

                // Cap points usage to subtotal after coupon
                $maxRedeemable = (int) floor(max($subtotal - $couponDiscount, 0));
                $pointsToUse = min($pointsToUse, $maxRedeemable);

                // Deduct points and record history
                $this->pointService->deductPoints($user, $pointsToUse, 'redeem', "Đổi điểm cho đơn {$order->order_code}");

                // Update order fields and total
                $order->update([
                    'points_used' => $pointsToUse,
                    'points_discount' => $pointsToUse,
                    'total_amount' => max($order->total_amount - $pointsToUse, 0),
                ]);
            }

            Payment::query()->create([
                'order_id' => $order->id,
                'payment_method' => $data['payment_method'],
                'amount' => $order->total_amount,
                'payment_status' => $data['payment_method'] === 'cod' ? 'pending' : 'paid',
                'transaction_code' => null,
                'paid_at' => null,
            ]);

            // Reserve IMEIs for order items; rollback if not enough stock
            $reserved = $this->imeiReservationService->reserve($order);

            if (! $reserved) {
                throw ValidationException::withMessages([
                    'inventory' => 'Không đủ IMEI cho một hoặc nhiều sản phẩm trong đơn hàng.',
                ]);
            }

            // Add points to user based on order total amount
            $pointsEarned = $this->pointService->calculatePointsFromOrder($order->total_amount);
            if ($pointsEarned > 0) {
                $this->pointService->addPoints(
                    $user,
                    $pointsEarned,
                    'purchase',
                    "Mua hàng - Đơn hàng #{$order->order_code}"
                );
            }

            $this->cartService->clear($user);

            return $order;
        });
    }
}

<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
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

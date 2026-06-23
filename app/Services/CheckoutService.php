<?php

namespace App\Services;

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

            $order = Order::query()->create([
                'user_id' => $user->id,
                'order_code' => strtoupper(Str::random(10)),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'shipping_address' => $data['shipping_address'],
                'subtotal' => $subtotal,
                'membership_discount' => 0,
                'coupon_discount' => 0,
                'total_amount' => $subtotal,
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

            $this->cartService->clear($user);

            return $order;
        });
    }
}

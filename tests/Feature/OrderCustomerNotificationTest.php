<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\PaymentSucceededNotification;
use App\Services\OrderCustomerNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderCustomerNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_receives_order_and_payment_notifications(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_code' => 'ORD-NOTIFICATION-001',
            'customer_name' => 'Test Customer',
            'customer_phone' => '0912345678',
            'shipping_address' => 'Test address',
            'subtotal' => 100000,
            'membership_discount' => 0,
            'coupon_discount' => 0,
            'points_used' => 0,
            'points_discount' => 0,
            'total_amount' => 100000,
            'status' => 'processing',
            'fulfillment_status' => 'pending',
        ]);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'card',
            'amount' => 100000,
            'payment_status' => 'paid',
            'transaction_code' => 'PAYMENT-001',
            'paid_at' => now(),
        ]);

        $service = app(OrderCustomerNotificationService::class);
        $service->orderPlaced($order);
        $service->paymentSucceeded($payment);

        Notification::assertSentTo($user, OrderPlacedNotification::class);
        Notification::assertSentTo($user, PaymentSucceededNotification::class);
    }
}

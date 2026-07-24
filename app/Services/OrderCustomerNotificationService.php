<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\PaymentSucceededNotification;

class OrderCustomerNotificationService
{
    public function orderPlaced(Order $order): void
    {
        $order->user?->notify(new OrderPlacedNotification($order));
    }

    public function paymentSucceeded(Payment $payment): void
    {
        $order = $payment->order;

        $order?->user?->notify(new PaymentSucceededNotification($order, $payment));
    }
}

<?php

namespace App\Notifications;

use App\Models\RefundRequest;
use Illuminate\Notifications\Notification;

class RefundCompletedNotification extends Notification
{
    public function __construct(
        private readonly RefundRequest $refund,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $amountText = number_format((float) $this->refund->amount, 0, ',', '.') . ' đ';
        $methodText = $this->refund->method === 'wallet' ? 'Ví ByteZone' : 'tài khoản ngân hàng của bạn';

        return [
            'type' => 'refund',
            'title' => 'Hoàn tiền thành công',
            'message' => 'Đã hoàn ' . $amountText . ' vào ' . $methodText . ' cho đơn hàng #' . $this->refund->order->order_code . '.',
            'url' => route('orders.show', $this->refund->order_id),
            'icon' => 'bi-arrow-counterclockwise',
        ];
    }
}

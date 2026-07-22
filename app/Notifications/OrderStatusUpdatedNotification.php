<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification
{
    private const LABELS = [
        'waiting_pack' => ['Đơn hàng đã được xác nhận', 'Đơn hàng :code đã được xác nhận và đang được chuẩn bị.', 'bi-check-circle'],
        'waiting_handover' => ['Đơn hàng đã đóng gói xong', 'Đơn hàng :code đã đóng gói xong, đang chờ bàn giao vận chuyển.', 'bi-box-seam'],
        'shipping' => ['Đơn hàng đang được giao', 'Đơn hàng :code đang trên đường giao đến bạn.', 'bi-truck'],
        'completed' => ['Giao hàng thành công', 'Đơn hàng :code đã được giao thành công. Cảm ơn bạn đã mua sắm!', 'bi-check2-circle'],
        'failed' => ['Giao hàng không thành công', 'Đơn hàng :code giao không thành công, chúng tôi sẽ liên hệ để giao lại.', 'bi-exclamation-triangle'],
        'cancelled' => ['Đơn hàng đã bị hủy', 'Đơn hàng :code đã bị hủy.', 'bi-x-circle'],
    ];

    public function __construct(
        private readonly Order $order,
        private readonly string $status,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        [$title, $message, $icon] = self::LABELS[$this->status] ?? [
            'Đơn hàng có cập nhật mới',
            'Đơn hàng :code vừa có cập nhật trạng thái.',
            'bi-bell',
        ];

        return [
            'type' => 'order',
            'title' => $title,
            'message' => str_replace(':code', $this->order->order_code, $message),
            'url' => route('orders.show', $this->order),
            'icon' => $icon,
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification
{
    private const LABELS = [
        'waiting_pack'     => ['Đơn hàng đã được xác nhận', 'Đơn hàng :code đã được xác nhận và đang được chuẩn bị.', 'bi-check-circle'],
        'waiting_handover' => ['Đơn hàng đã đóng gói xong', 'Đơn hàng :code đã đóng gói xong, đang chờ bàn giao vận chuyển.', 'bi-box-seam'],
        'shipping'         => ['Đơn hàng đang được giao', 'Đơn hàng :code đang trên đường giao đến bạn.', 'bi-truck'],
        'completed'        => ['Giao hàng thành công', 'Đơn hàng :code đã được giao thành công. Cảm ơn bạn đã mua sắm!', 'bi-check2-circle'],
        'failed'           => ['Giao hàng không thành công', 'Đơn hàng :code giao không thành công, chúng tôi sẽ liên hệ để giao lại.', 'bi-exclamation-triangle'],
        'cancelled'        => ['Đơn hàng đã bị hủy', 'Đơn hàng :code đã bị hủy.', 'bi-x-circle'],
    ];

    // Chỉ gửi email cho các trạng thái quan trọng
    private const MAIL_STATUSES = ['completed', 'cancelled', 'shipping'];

    public function __construct(
        private readonly Order $order,
        private readonly string $status,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (in_array($this->status, self::MAIL_STATUSES, true)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        [$title, $message, $icon] = self::LABELS[$this->status] ?? [
            'Đơn hàng có cập nhật mới',
            'Đơn hàng :code vừa có cập nhật trạng thái.',
            'bi-bell',
        ];

        return [
            'type'    => 'order',
            'title'   => $title,
            'message' => str_replace(':code', $this->order->order_code, $message),
            'url'     => route('orders.show', $this->order),
            'icon'    => $icon,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        [$title, $message] = self::LABELS[$this->status] ?? [
            'Đơn hàng có cập nhật mới',
            'Đơn hàng :code vừa có cập nhật trạng thái.',
        ];

        $message = str_replace(':code', $this->order->order_code, $message);

        return (new MailMessage)
            ->subject($title . ' - ' . config('app.name'))
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line($message)
            ->action('Xem chi tiết đơn hàng', config('app.url') . '/orders/' . $this->order->id)
            ->salutation('Trân trọng, ' . config('app.name'));
    }
}

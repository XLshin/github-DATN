<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Giao dịch thanh toán thất bại - Đơn hàng ' . $this->order->order_code . ' - ' . config('app.name'))
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Giao dịch chuyển tiền cho đơn hàng ' . $this->order->order_code . ' đã thất bại.')
            ->line('Vui lòng kiểm tra lại giao dịch ngân hàng của bạn.')
            ->line('Đơn hàng đã được tự động hủy do chưa hoàn tất thanh toán.')
            ->line('Nếu bạn đã chuyển tiền thành công nhưng nhận được thông báo này, vui lòng liên hệ bộ phận hỗ trợ kèm theo bằng chứng giao dịch để được kiểm tra lại.')
            ->salutation('Trân trọng, ' . config('app.name'));
    }
}

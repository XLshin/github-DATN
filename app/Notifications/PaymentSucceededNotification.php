<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSucceededNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Order $order,
        private readonly Payment $payment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'payment',
            'title' => 'Thanh toán thành công',
            'message' => "Thanh toán cho đơn hàng {$this->order->order_code} đã thành công.",
            'url' => route('orders.show', $this->order),
            'icon' => 'bi-credit-card-2-front',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Thanh toán thành công - Đơn hàng {$this->order->order_code} - " . config('app.name'))
            ->greeting("Xin chào {$notifiable->name}!")
            ->line("Thanh toán cho đơn hàng {$this->order->order_code} đã thành công.")
            ->line('Số tiền: ' . number_format((float) $this->payment->amount, 0, ',', '.') . ' đ')
            ->line('Mã giao dịch: ' . ($this->payment->transaction_code ?: 'Đang cập nhật'))
            ->line('Thời gian thanh toán: ' . ($this->payment->paid_at?->format('H:i d/m/Y') ?: now()->format('H:i d/m/Y')))
            ->action('Xem chi tiết đơn hàng', config('app.url') . '/orders/' . $this->order->id)
            ->salutation('Trân trọng, ' . config('app.name'));
    }
}

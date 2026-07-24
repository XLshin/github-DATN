<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $isCod = $this->order->payment?->payment_method === 'cod';

        return [
            'type' => 'order',
            'title' => 'Da nhan don hang',
            'message' => $isCod
                ? "Don hang {$this->order->order_code} da duoc ghi nhan. Ban se thanh toan khi nhan hang."
                : "Don hang {$this->order->order_code} da duoc ghi nhan. Vui long hoan tat thanh toan.",
            'url' => route('orders.show', $this->order),
            'icon' => 'bi-bag-check',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payment = $this->order->payment;
        $paymentMessage = $payment?->payment_method === 'cod'
            ? 'Ban se thanh toan khi nhan hang.'
            : 'Don hang dang cho thanh toan hoac doi soat giao dich.';

        return (new MailMessage)
            ->subject("Xac nhan dat hang {$this->order->order_code} - " . config('app.name'))
            ->greeting("Xin chao {$notifiable->name}!")
            ->line("Chung toi da nhan don hang {$this->order->order_code} cua ban.")
            ->line('Tong thanh toan: ' . number_format((float) $this->order->total_amount, 0, ',', '.') . ' d')
            ->line($paymentMessage)
            ->action('Xem chi tiet don hang', config('app.url') . '/orders/' . $this->order->id)
            ->salutation('Tran trong, ' . config('app.name'));
    }
}

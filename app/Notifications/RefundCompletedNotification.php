<?php

namespace App\Notifications;

use App\Models\RefundRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundCompletedNotification extends Notification
{
    public function __construct(
        private readonly RefundRequest $refund,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
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

    public function toMail(object $notifiable): MailMessage
    {
        $refund = $this->refund;
        $amountText = number_format((float) $refund->amount, 0, ',', '.') . ' đ';
        $isWallet = $refund->method === 'wallet';

        $mail = (new MailMessage)
            ->subject('ByteZone - Hoàn tiền đơn hàng #' . $refund->order->order_code . ' thành công')
            ->greeting('Xin chào ' . ($notifiable->name ?? '') . ',')
            ->line('Chúng tôi đã hoàn tiền thành công cho đơn hàng #' . $refund->order->order_code . '. Chi tiết:')
            ->line('**Số tiền:** ' . $amountText)
            ->line('**Hình thức nhận:** ' . ($isWallet ? 'Ví ByteZone' : 'Chuyển khoản ngân hàng'));

        if (! $isWallet) {
            $mail->line('**Ngân hàng nhận:** ' . $refund->bank_name)
                ->line('**Số tài khoản:** ' . $refund->maskedBankAccountNumber())
                ->line('**Chủ tài khoản:** ' . $refund->bank_account_name);
        }

        return $mail
            ->line('**Thời gian:** ' . ($refund->completed_at?->format('H:i:s d/m/Y') ?? now()->format('H:i:s d/m/Y')))
            ->action('Xem chi tiết đơn hàng', route('orders.show', $refund->order_id))
            ->line('Cảm ơn bạn đã mua sắm tại ByteZone!');
    }
}

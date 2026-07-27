<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletCreditedNotification extends Notification
{
    public function __construct(
        private readonly float $amount,
        private readonly string $reason,
        private readonly ?string $transactionCode = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'wallet',
            'title' => 'Ví của bạn vừa được cộng tiền',
            'message' => number_format($this->amount, 0, ',', '.') . ' đ đã được cộng vào ví. ' . $this->reason,
            'url' => route('wallet.index'),
            'icon' => 'bi-wallet2',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amountText = number_format($this->amount, 0, ',', '.') . ' đ';

        $mail = (new MailMessage)
            ->subject('ByteZone - Ví của bạn vừa được cộng tiền')
            ->greeting('Xin chào ' . ($notifiable->name ?? '') . ',')
            ->line('Ví ByteZone của bạn vừa được cộng tiền thành công. Chi tiết:')
            ->line('**Số tiền:** ' . $amountText)
            ->line('**Nội dung:** ' . $this->reason);

        if ($this->transactionCode) {
            $mail->line('**Mã giao dịch:** ' . $this->transactionCode);
        }

        return $mail
            ->line('**Thời gian:** ' . now()->format('H:i:s d/m/Y'))
            ->action('Xem số dư ví', route('wallet.index'))
            ->line('Cảm ơn bạn đã sử dụng ByteZone!');
    }
}

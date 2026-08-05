<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletCreditedNotification extends Notification
{
    public function __construct(
        private readonly float $amount,
        private readonly string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'wallet',
            'title'   => 'Ví của bạn vừa được cộng tiền',
            'message' => number_format($this->amount, 0, ',', '.') . ' đ đã được cộng vào ví. ' . $this->reason,
            'url'     => route('wallet.index'),
            'icon'    => 'bi-wallet2',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amountText = number_format($this->amount, 0, ',', '.') . ' đ';

        return (new MailMessage)
            ->subject('Ví của bạn vừa được cộng tiền - ' . config('app.name'))
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line($amountText . ' đã được cộng vào ví ByteZone của bạn.')
            ->line('Lý do: ' . $this->reason)
            ->action('Xem ví của tôi', config('app.url') . '/wallet')
            ->salutation('Trân trọng, ' . config('app.name'));
    }
}

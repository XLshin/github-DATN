<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class WalletCreditedNotification extends Notification
{
    public function __construct(
        private readonly float $amount,
        private readonly string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
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
}

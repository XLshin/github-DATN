<?php

namespace App\Notifications;

use App\Models\WalletWithdrawal;
use Illuminate\Notifications\Notification;

class WithdrawalCompletedNotification extends Notification
{
    public function __construct(
        private readonly WalletWithdrawal $withdrawal,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $amountText = number_format((float) $this->withdrawal->amount, 0, ',', '.') . ' đ';

        return [
            'type' => 'withdrawal',
            'title' => 'Rút tiền thành công',
            'message' => 'Đã chuyển ' . $amountText . ' về tài khoản ' . $this->withdrawal->bank_name
                . ' của bạn.',
            'url' => route('wallet.index'),
            'icon' => 'bi-bank2',
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\WalletWithdrawal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalCompletedNotification extends Notification
{
    public function __construct(
        private readonly WalletWithdrawal $withdrawal,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $amountText = number_format((float) $this->withdrawal->amount, 0, ',', '.') . ' đ';

        return [
            'type'    => 'withdrawal',
            'title'   => 'Rút tiền thành công',
            'message' => 'Đã chuyển ' . $amountText . ' về tài khoản ' . $this->withdrawal->bank_name . ' của bạn.',
            'url'     => route('wallet.index'),
            'icon'    => 'bi-bank2',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amountText = number_format((float) $this->withdrawal->amount, 0, ',', '.') . ' đ';
        $accountText = $this->withdrawal->bank_name . ' (' . $this->withdrawal->account_holder_name . ')';

        return (new MailMessage)
            ->subject('Rút tiền thành công - ' . config('app.name'))
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Yêu cầu rút tiền của bạn đã được xử lý thành công.')
            ->line('Số tiền: ' . $amountText)
            ->line('Tài khoản nhận: ' . $accountText)
            ->action('Xem lịch sử ví', config('app.url') . '/wallet')
            ->salutation('Trân trọng, ' . config('app.name'));
    }
}

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
        $w = $this->withdrawal;
        $amountText = number_format((float) $w->amount, 0, ',', '.') . ' đ';
        $maskedAccount = $this->maskAccountNumber((string) $w->account_number);

        return (new MailMessage)
            ->subject('ByteZone - Yêu cầu rút tiền đã hoàn tất')
            ->greeting('Xin chào ' . ($notifiable->name ?? '') . ',')
            ->line('Yêu cầu rút tiền của bạn đã được xử lý thành công. Chi tiết giao dịch:')
            ->line('**Số tiền:** ' . $amountText)
            ->line('**Ngân hàng nhận:** ' . $w->bank_name)
            ->line('**Số tài khoản:** ' . $maskedAccount)
            ->line('**Chủ tài khoản:** ' . $w->account_holder_name)
            ->when($w->transaction_code, fn ($mail) => $mail->line('**Mã giao dịch:** ' . $w->transaction_code))
            ->line('**Thời gian:** ' . ($w->completed_at?->format('H:i:s d/m/Y') ?? now()->format('H:i:s d/m/Y')))
            ->action('Xem chi tiết ví', route('wallet.index'))
            ->line('Cảm ơn bạn đã sử dụng ByteZone!');
    }
}

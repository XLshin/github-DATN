<?php

namespace App\Notifications;

use App\Models\WalletWithdrawal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRejectedNotification extends Notification
{
    public function __construct(
        private readonly WalletWithdrawal $withdrawal,
        private readonly string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $amountText = number_format((float) $this->withdrawal->amount, 0, ',', '.') . ' đ';

        return [
            'type' => 'withdrawal',
            'title' => 'Yêu cầu rút tiền bị từ chối',
            'message' => 'Yêu cầu rút ' . $amountText . ' về ' . $this->withdrawal->bank_name
                . ' đã bị từ chối. Lý do: ' . $this->reason . '. Số dư đã được hoàn lại vào ví.',
            'url' => route('wallet.index'),
            'icon' => 'bi-x-circle',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $w = $this->withdrawal;
        $amountText = number_format((float) $w->amount, 0, ',', '.') . ' đ';

        return (new MailMessage)
            ->subject('ByteZone - Yêu cầu rút tiền bị từ chối')
            ->greeting('Xin chào ' . ($notifiable->name ?? '') . ',')
            ->line('Rất tiếc, yêu cầu rút tiền của bạn đã bị từ chối. Chi tiết:')
            ->line('**Số tiền:** ' . $amountText)
            ->line('**Ngân hàng nhận (yêu cầu):** ' . $w->bank_name . ' — ' . $this->maskAccountNumber((string) $w->account_number))
            ->line('**Lý do từ chối:** ' . $this->reason)
            ->line('Số dư ' . $amountText . ' đã được hoàn lại đầy đủ vào Ví ByteZone của bạn.')
            ->action('Xem ví của bạn', route('wallet.index'))
            ->line('Nếu cần hỗ trợ thêm, vui lòng liên hệ ByteZone.');
    }

    private function maskAccountNumber(string $accountNumber): string
    {
        $length = strlen($accountNumber);

        if ($length <= 4) {
            return $accountNumber;
        }

        return str_repeat('*', $length - 4) . substr($accountNumber, -4);
    }
}

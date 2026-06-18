<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Mật khẩu đã được thay đổi - '.config('app.name'))
            ->greeting('Xin chào '.$notifiable->name.'!')
            ->line('Mật khẩu tài khoản của bạn vừa được thay đổi thành công.')
            ->line('Thời gian: '.now()->timezone(config('app.timezone'))->format('d/m/Y H:i:s'))
            ->line('Nếu bạn không thực hiện thay đổi này, vui lòng liên hệ bộ phận hỗ trợ ngay lập tức.')
            ->salutation('Trân trọng, '.config('app.name'));
    }
}

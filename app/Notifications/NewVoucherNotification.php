<?php

namespace App\Notifications;

use App\Models\Coupon;
use Illuminate\Notifications\Notification;

class NewVoucherNotification extends Notification
{
    public function __construct(
        private readonly Coupon $coupon,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $discountText = $this->coupon->discount_type === 'percent'
            ? $this->coupon->discount_value . '%'
            : number_format((float) $this->coupon->discount_value, 0, ',', '.') . ' đ';

        return [
            'type' => 'voucher',
            'title' => 'Voucher mới: ' . $this->coupon->code,
            'message' => 'Giảm ' . $discountText . ' cho đơn từ '
                . number_format((float) $this->coupon->min_order_amount, 0, ',', '.') . ' đ. Nhanh tay lưu voucher!',
            'url' => route('client.vouchers.index'),
            'icon' => 'bi-ticket-perforated',
        ];
    }
}

<?php

namespace App\Providers;

use App\View\Composers\AdminNotificationComposer;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Fix lỗi pagination Laravel bị vỡ layout, icon Previous/Next quá lớn
        Paginator::useBootstrapFive();

        // Khi chạy sau proxy TLS-terminating (ngrok, ...), request nội bộ tới php artisan serve
        // luôn là http nên asset()/url() tự sinh sai thành http:// dù APP_URL là https:// — ép
        // scheme theo APP_URL để tránh trình duyệt chặn mixed content.
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Chuông thông báo các yêu cầu khách hàng đang chờ admin xử lý (nạp ví, rút tiền, hoàn tiền, thanh toán)
        View::composer('partials.admin.header', AdminNotificationComposer::class);
    }
}

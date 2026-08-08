<?php

namespace App\Providers;

use App\View\Composers\AdminNotificationComposer;
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

        // Chuông thông báo các yêu cầu khách hàng đang chờ admin xử lý (nạp ví, rút tiền, hoàn tiền, thanh toán)
        View::composer('partials.admin.header', AdminNotificationComposer::class);
    }
}

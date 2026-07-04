<?php

namespace App\Providers;

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
    }
}

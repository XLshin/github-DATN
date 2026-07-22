<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\ReleaseStaleImeis::class,
        \App\Console\Commands\SyncBannerSchedule::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // run every 5 minutes to release stale imeis
        $schedule->command('imei:release-stale')->everyFiveMinutes();

        // Đồng bộ lịch hẹn bật/tắt banner mỗi phút
        $schedule->command('banner:sync-schedule')->everyMinute();

        // Xác nhận các giao dịch mô phỏng (thanh toán/nạp ví/hoàn tiền) đến hạn, để chúng tự xử
        // lý dù không có ai đang mở đúng trang để kích hoạt (checkout/wallet/order polling).
        $schedule->command('transactions:confirm-simulated')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}

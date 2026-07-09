<?php

namespace App\Console\Commands;

use App\Models\Banner;
use Illuminate\Console\Command;

class SyncBannerSchedule extends Command
{
    protected $signature   = 'banner:sync-schedule';
    protected $description = 'Tự động bật/tắt banner theo lịch hẹn giờ';

    public function handle(): void
    {
        $now = now();

        // Bật banner đã đến giờ bắt đầu (starts_at <= now, status=false, chưa hết hạn)
        $activated = Banner::where('status', false)
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', $now);
            })
            ->update(['status' => true]);

        // Tắt banner đã hết hạn
        $deactivated = Banner::where('status', true)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', $now)
            ->update(['status' => false]);

        $this->info("Đã bật: {$activated} banner · Đã tắt: {$deactivated} banner");
    }
}

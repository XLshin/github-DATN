<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Imei;
use Carbon\Carbon;

class ReleaseStaleImeis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imei:release-stale {--ttl=15 : TTL in minutes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release IMEIs reserved longer than TTL minutes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ttl = (int) $this->option('ttl');
        $threshold = Carbon::now()->subMinutes($ttl);

        $stale = Imei::where('status', 'reserved')
            ->whereNotNull('reserved_at')
            ->where('reserved_at', '<', $threshold)
            ->get();

        $count = $stale->count();

        foreach ($stale as $imei) {
            $imei->status = 'available';
            $imei->reserved_at = null;
            $imei->reserved_by_order_item_id = null;
            $imei->save();
        }

        $this->info("Released {$count} stale IMEIs (older than {$ttl} minutes)");

        return 0;
    }
}

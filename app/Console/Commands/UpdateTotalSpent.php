<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;

class UpdateTotalSpent extends Command
{
    protected $signature = 'update:total-spent';
    protected $description = 'Cập nhật total_spent cho tất cả khách hàng từ các đơn hàng đã hoàn thành';

    public function handle()
    {
        $this->info('Đang cập nhật tổng chi tiêu...');

        User::where('role', 'customer')->chunk(100, function ($users) {
            foreach ($users as $user) {
                $total = Order::where('user_id', $user->id)
                    ->where('fulfillment_status', 'completed') // hoặc 'delivered' tùy logic
                    ->sum('total_amount');

                $user->update(['total_spent' => $total]);
            }
        });

        $this->info('Hoàn tất!');
    }
}

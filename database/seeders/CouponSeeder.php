<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('coupons')->updateOrInsert(
            ['code' => 'SALE20'],
            [
                'discount_type' => 'percent',
                'discount_value' => 20,
                'min_order_amount' => 10000000,
                'usage_limit' => 50,
                'used_count' => 0,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'status' => true,
            ]
        );
    }
}

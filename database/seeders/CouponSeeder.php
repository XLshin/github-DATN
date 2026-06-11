<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Coupon::insert([

    [

        'code' => 'SALE10',

        'discount_type' => 'percent',

        'discount_value' => 10,

        'min_order_amount' => 5000000,

        'usage_limit' => 100,

        'used_count' => 0,

        'start_date' => now(),

        'end_date' => now()->addMonths(1),

        'status' => true

    ]

]);
    }
}

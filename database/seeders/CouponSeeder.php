<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $coupons = [
            // Giảm % có điều kiện đơn tối thiểu
            [
                'code'             => 'SALE20',
                'discount_type'    => 'percent',
                'discount_value'   => 20,
                'min_order_amount' => 10000000,
                'usage_limit'      => 50,
                'used_count'       => 0,
                'start_date'       => $now,
                'end_date'         => $now->copy()->addMonths(2),
                'status'           => true,
            ],
            // Giảm % nhỏ không có điều kiện
            [
                'code'             => 'WELCOME10',
                'discount_type'    => 'percent',
                'discount_value'   => 10,
                'min_order_amount' => 0,
                'usage_limit'      => 100,
                'used_count'       => 0,
                'start_date'       => $now,
                'end_date'         => $now->copy()->addMonths(3),
                'status'           => true,
            ],
            // Giảm tiền cố định
            [
                'code'             => 'GIAM500K',
                'discount_type'    => 'fixed',
                'discount_value'   => 500000,
                'min_order_amount' => 5000000,
                'usage_limit'      => 30,
                'used_count'       => 0,
                'start_date'       => $now,
                'end_date'         => $now->copy()->addMonth(),
                'status'           => true,
            ],
            // Giảm tiền lớn
            [
                'code'             => 'VIP2000K',
                'discount_type'    => 'fixed',
                'discount_value'   => 2000000,
                'min_order_amount' => 20000000,
                'usage_limit'      => 10,
                'used_count'       => 0,
                'start_date'       => $now,
                'end_date'         => $now->copy()->addMonth(),
                'status'           => true,
            ],
            // Coupon hết hạn (để test trường hợp expired)
            [
                'code'             => 'EXPIRED50',
                'discount_type'    => 'percent',
                'discount_value'   => 50,
                'min_order_amount' => 0,
                'usage_limit'      => 5,
                'used_count'       => 5,
                'start_date'       => $now->copy()->subMonths(2),
                'end_date'         => $now->copy()->subMonth(),
                'status'           => false,
            ],
        ];

        foreach ($coupons as $coupon) {
            DB::table('coupons')->updateOrInsert(
                ['code' => $coupon['code']],
                array_merge($coupon, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}

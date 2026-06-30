<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PointHistorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $users = DB::table('users')->where('role', 'customer')->get();

        if ($users->isEmpty()) {
            throw new \Exception('Cần có users trước khi seed point_histories.');
        }

        DB::table('point_histories')->truncate();

        foreach ($users as $user) {
            // Earn points từ mua hàng
            DB::table('point_histories')->insert([
                [
                    'user_id' => $user->id,
                    'points' => 300,
                    'type' => 'earn',
                    'description' => 'Tích điểm từ đơn hàng ORD_TEST_SHIPMENT_001',
                    'created_at' => $now->copy()->subDays(10),
                    'updated_at' => $now->copy()->subDays(10),
                ],
                [
                    'user_id' => $user->id,
                    'points' => 50,
                    'type' => 'earn',
                    'description' => 'Điểm thưởng đăng ký thành viên',
                    'created_at' => $now->copy()->subDays(20),
                    'updated_at' => $now->copy()->subDays(20),
                ],
                [
                    'user_id' => $user->id,
                    'points' => -100,
                    'type' => 'redeem',
                    'description' => 'Dùng điểm giảm giá đơn hàng',
                    'created_at' => $now->copy()->subDays(5),
                    'updated_at' => $now->copy()->subDays(5),
                ],
            ]);
        }
    }
}

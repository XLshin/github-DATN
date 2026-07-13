<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouponUserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $customers = DB::table('users')->where('role', 'customer')->get();
        $coupons   = DB::table('coupons')->where('status', true)->get();

        if ($customers->isEmpty() || $coupons->isEmpty()) {
            $this->command->warn('Chưa có customer hoặc coupon để gán.');
            return;
        }

        // Gán chỉ coupon đã được đánh dấu là assigned cho mỗi customer
        foreach ($customers as $customer) {
            foreach ($coupons as $coupon) {
                if ($coupon->distribution !== \App\Models\Coupon::DISTRIBUTION_ASSIGNED) {
                    continue;
                }

                DB::table('coupon_user')->updateOrInsert(
                    ['coupon_id' => $coupon->id, 'user_id' => $customer->id],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        $this->command->info('Đã gán coupon cho ' . $customers->count() . ' customer.');
    }
}

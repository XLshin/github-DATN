<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,

            CategorySeeder::class,
            BrandSeeder::class,

            ProductSeeder::class,
            ProductVariantSeeder::class,
            ProductImageSeeder::class,

            CouponSeeder::class,
            BannerSeeder::class,

            ImeiSeeder::class,

            OrderSeeder::class,

            // Tạo 1 đơn hàng test thực tế từ IMEI + biến thể có sẵn
            RealOrderTestSeeder::class,

            ReviewSeeder::class,
            PointHistorySeeder::class,

            WarrantyTestSeeder::class,
        ]);
    }
}
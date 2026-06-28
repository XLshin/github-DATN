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
            ReviewSeeder::class,
            PointHistorySeeder::class,

            // Bảo hành cần có product_variants, imeis, orders, order_items trước
            WarrantyTestSeeder::class,
        ]);
    }
}
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

            CarrierSeeder::class,

            ImeiSeeder::class,

            // Skip OrderSeeder in testing to avoid sqlite migration/seed edge-cases
            // OrderSeeder prepares sample orders and may run complex SQL incompatible with sqlite.
        ]);

        if (! app()->environment('testing')) {
            $this->call([
                OrderSeeder::class,
                ReviewSeeder::class,
                PointHistorySeeder::class,
                // Warranty depends on orders & imeis
                WarrantyTestSeeder::class,
            ]);
        }
    }
}

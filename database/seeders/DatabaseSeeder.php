<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,

            // // existing seeders (if any) will run first
            // CategorySeeder::class,
            // BrandSeeder::class,
            // ProductSeeder::class,
            // CouponSeeder::class,
            // BannerSeeder::class,
            // ProductVariantSeeder::class,

            // // our sample data for testing
            // SampleDataSeeder::class,
        ]);
    }
}

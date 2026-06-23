<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\PointHistory;
use App\Models\Coupon;
use App\Models\Banner;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductImage;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // UserSeeder::class,

            CategorySeeder::class,
            BrandSeeder::class,

            ProductSeeder::class,
            ProductVariantSeeder::class,

            CouponSeeder::class,
            BannerSeeder::class,

            OrderSeeder::class,

            // Nếu có seeder bảo hành thì để sau OrderSeeder
            WarrantyTestSeeder::class,
        ]);
    }
}

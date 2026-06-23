<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::query()->value('id');

        $appleId = Brand::where('name', 'Apple')->value('id');
        $samsungId = Brand::where('name', 'Samsung')->value('id');

        if (!$categoryId) {
            throw new \Exception('Chưa có dữ liệu trong bảng categories. Hãy chạy CategorySeeder trước.');
        }

        if (!$appleId || !$samsungId) {
            throw new \Exception('Chưa có brand Apple hoặc Samsung. Hãy kiểm tra BrandSeeder.');
        }

        Product::updateOrCreate(
            ['slug' => 'iphone-16-pro'],
            [
                'category_id' => $categoryId,
                'brand_id' => $appleId,
                'name' => 'iPhone 16 Pro',
                'description' => 'iPhone 16 Pro chính hãng',
                'price' => 29990000,
                'stock_quantity' => 50,
                'status' => true,
            ]
        );

        Product::updateOrCreate(
            ['slug' => 'samsung-galaxy-s25'],
            [
                'category_id' => $categoryId,
                'brand_id' => $samsungId,
                'name' => 'Samsung Galaxy S25',
                'description' => 'Samsung Galaxy S25 chính hãng',
                'price' => 24990000,
                'stock_quantity' => 30,
                'status' => true,
            ]
        );
    }
}

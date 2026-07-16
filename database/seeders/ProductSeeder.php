<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryNames = ['Điện thoại', 'Máy tính bảng', 'Phụ kiện'];
        $brandNames = ['Apple', 'Samsung', 'Xiaomi', 'Oppo'];

        $categories = Category::whereIn('name', $categoryNames)->get()->keyBy('name');
        $brands = Brand::whereIn('name', $brandNames)->get()->keyBy('name');

        $missingCategories = array_diff($categoryNames, $categories->keys()->toArray());
        $missingBrands = array_diff($brandNames, $brands->keys()->toArray());

        if (!empty($missingCategories)) {
            throw new \Exception('Thiếu danh mục: ' . implode(', ', $missingCategories) . '. Hãy chạy CategorySeeder trước.');
        }

        if (!empty($missingBrands)) {
            throw new \Exception('Thiếu thương hiệu: ' . implode(', ', $missingBrands) . '. Hãy chạy BrandSeeder trước.');
        }

        $products = [
            [
                'slug' => 'iphone-16-pro',
                'name' => 'iPhone 16 Pro',

                'category' => 'Điện thoại',
                'brand' => 'Apple',
                'description' => 'iPhone 16 Pro chính hãng, thiết kế mới và hiệu năng vượt trội.',
                'price' => 29990000,
                'stock_quantity' => 50,
                'status' => true,
                'product_type' => 'imei/serial',
            ],
            [
                'slug' => 'samsung-galaxy-s25',
                'name' => 'Samsung Galaxy S25',
                'storage' => '256GB',
                'category' => 'Điện thoại',
                'brand' => 'Samsung',
                'description' => 'Samsung Galaxy S25 chính hãng với màn hình siêu sắc nét.',
                'price' => 24990000,
                'stock_quantity' => 30,
                'status' => true,
                'product_type' => 'imei/serial',
            ],
            [
                'slug' => 'xiaomi-redmi-note-15',
                'name' => 'Xiaomi Redmi Note 15',
                'storage' => '128GB',
                'category' => 'Điện thoại',
                'brand' => 'Xiaomi',
                'description' => 'Xiaomi Redmi Note 15 pin khỏe, hiệu năng tốt trong tầm giá.',
                'price' => 6990000,
                'stock_quantity' => 40,
                'status' => true,
                'product_type' => 'quantity',
            ],
            [
                'slug' => 'oppo-reno-12',
                'name' => 'Oppo Reno 12',
                'storage' => '128GB',
                'category' => 'Điện thoại',
                'brand' => 'Oppo',
                'description' => 'Oppo Reno 12 camera đẹp, thiết kế mỏng nhẹ.',
                'price' => 8990000,
                'stock_quantity' => 35,
                'status' => true,
                'product_type' => 'quantity',
            ],
            [
                'slug' => 'ipad-air-6',
                'name' => 'iPad Air 6',
                'storage' => '256GB',
                'category' => 'Máy tính bảng',
                'brand' => 'Apple',
                'description' => 'iPad Air 6 mỏng nhẹ, hiệu năng mạnh mẽ cho công việc và giải trí.',
                'price' => 22990000,
                'stock_quantity' => 20,
                'status' => true,
                'product_type' => 'quantity',
            ],
            [
                'slug' => 'samsung-galaxy-tab-s10',
                'name' => 'Samsung Galaxy Tab S10',
                'storage' => '128GB',
                'category' => 'Máy tính bảng',
                'brand' => 'Samsung',
                'description' => 'Samsung Galaxy Tab S10 màn hình lớn, âm thanh sống động.',
                'price' => 19990000,
                'stock_quantity' => 18,
                'status' => true,
                'product_type' => 'quantity',
            ],
            [
                'slug' => 'apple-airpods-pro-3',
                'name' => 'Apple AirPods Pro 3',
                'storage' => '1 unit',
                'category' => 'Phụ kiện',
                'brand' => 'Apple',
                'description' => 'AirPods Pro 3 chống ồn chủ động, âm thanh chất lượng.',
                'price' => 5990000,
                'stock_quantity' => 45,
                'status' => true,
                'product_type' => 'quantity',
            ],
            [
                'slug' => 'samsung-galaxy-buds-3',
                'name' => 'Samsung Galaxy Buds 3',
                'storage' => '1 unit',
                'category' => 'Phụ kiện',
                'brand' => 'Samsung',
                'description' => 'Galaxy Buds 3 tiện lợi, pin bền và âm thanh rõ nét.',
                'price' => 2490000,
                'stock_quantity' => 38,
                'status' => true,
                'product_type' => 'quantity',
            ],
            [
                'slug' => 'xiaomi-65w-charger',
                'name' => 'Xiaomi 65W Charger',
                'storage' => '65W',
                'category' => 'Phụ kiện',
                'brand' => 'Xiaomi',
                'description' => 'Sạc nhanh 65W tương thích nhiều thiết bị.',
                'price' => 490000,
                'stock_quantity' => 60,
                'status' => true,
                'product_type' => 'quantity',
            ],
        ];

        foreach ($products as $productData) {
            $productGroup = ProductGroup::updateOrCreate(
                ['slug' => Str::slug($productData['name'])],
                [
                    'category_id' => $categories[$productData['category']]->id,
                    'brand_id' => $brands[$productData['brand']]->id,
                    'name' => $productData['name'],
                    'slug' => Str::slug($productData['name']),
                    'description' => $productData['description'],
                    'status' => $productData['status'],
                    'product_type' => $productData['product_type'],
                ]
            );

            Product::updateOrCreate(
                ['slug' => $productData['slug']],
                [
                    'category_id' => $categories[$productData['category']]->id,
                    'brand_id' => $brands[$productData['brand']]->id,
                    'product_group_id' => $productGroup->id,
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'stock_quantity' => $productData['stock_quantity'],
                    // 'storage' => $productData['storage'],
                    'thumbnail' => null,
                    'status' => $productData['status'],
                    'product_type' => $productData['product_type'],
                ]
            );
        }
    }
}

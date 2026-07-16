<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $variantSets = [
            'iphone-16-pro' => [
                ['color' => 'Titan Đen', 'storage' => '128GB', 'stock_quantity' => 10, 'additional_price' => 0, 'status' => true],
                ['color' => 'Titan Đen', 'storage' => '256GB', 'stock_quantity' => 8, 'additional_price' => 3000000, 'status' => true],
                ['color' => 'Titan Trắng', 'storage' => '128GB', 'stock_quantity' => 15, 'additional_price' => 0, 'status' => true],
                ['color' => 'Titan Trắng', 'storage' => '256GB', 'stock_quantity' => 12, 'additional_price' => 3000000, 'status' => true],
                ['color' => 'Titan Sa Mạc', 'storage' => '128GB', 'stock_quantity' => 10, 'additional_price' => 0, 'status' => true],
                ['color' => 'Titan Sa Mạc', 'storage' => '256GB', 'stock_quantity' => 8, 'additional_price' => 3000000, 'status' => true],
            ],
            'samsung-galaxy-s25' => [
                ['color' => 'Đen', 'storage' => '256GB', 'stock_quantity' => 20, 'additional_price' => 0, 'status' => true],
                ['color' => 'Bạc', 'storage' => '512GB', 'stock_quantity' => 10, 'additional_price' => 2000000, 'status' => true],
            ],
            'xiaomi-redmi-note-15' => [
                ['color' => 'Đen', 'storage' => '128GB', 'stock_quantity' => 25, 'additional_price' => 0, 'status' => true],
                ['color' => 'Xanh', 'storage' => '256GB', 'stock_quantity' => 18, 'additional_price' => 1000000, 'status' => true],
            ],
            'oppo-reno-12' => [
                ['color' => 'Xanh', 'storage' => '128GB', 'stock_quantity' => 22, 'additional_price' => 0, 'status' => true],
                ['color' => 'Hồng', 'storage' => '256GB', 'stock_quantity' => 16, 'additional_price' => 1200000, 'status' => true],
            ],
            'ipad-air-6' => [
                ['color' => 'Xám', 'storage' => '128GB', 'stock_quantity' => 12, 'additional_price' => 0, 'status' => true],
                ['color' => 'Bạc', 'storage' => '256GB', 'stock_quantity' => 7, 'additional_price' => 2500000, 'status' => true],
            ],
            'samsung-galaxy-tab-s10' => [
                ['color' => 'Đen', 'storage' => '256GB', 'stock_quantity' => 14, 'additional_price' => 0, 'status' => true],
                ['color' => 'Trắng', 'storage' => '512GB', 'stock_quantity' => 6, 'additional_price' => 3500000, 'status' => true],
            ],
            'apple-airpods-pro-3' => [
                ['color' => 'Trắng', 'storage' => '1 unit', 'stock_quantity' => 45, 'additional_price' => 0, 'status' => true],
            ],
            'samsung-galaxy-buds-3' => [
                ['color' => 'Đen', 'storage' => '1 unit', 'stock_quantity' => 38, 'additional_price' => 0, 'status' => true],
            ],
            'xiaomi-65w-charger' => [
                ['color' => 'Trắng', 'storage' => '65W', 'stock_quantity' => 60, 'additional_price' => 0, 'status' => true],
            ],
        ];

        foreach ($variantSets as $slug => $variants) {
            $product = Product::where('slug', $slug)->first();

            if (!$product) {
                throw new \Exception("Không tìm thấy sản phẩm với slug {$slug}. Hãy chạy ProductSeeder trước ProductVariantSeeder.");
            }

            foreach ($variants as $variant) {
                ProductVariant::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'color' => $variant['color'],
                    ],
                    [
                        'stock_quantity' => $variant['stock_quantity'],
                        'additional_price' => $variant['additional_price'],
                        'status' => $variant['status'],
                    ]
                );
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $iphone = Product::where('slug', 'iphone-16-pro')->first();

        if (!$iphone) {
            throw new \Exception('Không tìm thấy sản phẩm iPhone 16 Pro. Hãy chạy ProductSeeder trước ProductVariantSeeder.');
        }

        $variants = [
            [
                'color' => 'Titan Đen',
                'storage' => '128GB',
                'stock_quantity' => 10,
                'additional_price' => 0,
                'status' => true,
            ],
            [
                'color' => 'Titan Đen',
                'storage' => '256GB',
                'stock_quantity' => 8,
                'additional_price' => 3000000,
                'status' => true,
            ],
            [
                'color' => 'Titan Trắng',
                'storage' => '128GB',
                'stock_quantity' => 15,
                'additional_price' => 0,
                'status' => true,
            ],
            [
                'color' => 'Titan Trắng',
                'storage' => '256GB',
                'stock_quantity' => 12,
                'additional_price' => 3000000,
                'status' => true,
            ],
            [
                'color' => 'Titan Sa Mạc',
                'storage' => '128GB',
                'stock_quantity' => 5,
                'additional_price' => 0,
                'status' => true,
            ],
            [
                'color' => 'Titan Sa Mạc',
                'storage' => '256GB',
                'stock_quantity' => 3,
                'additional_price' => 3000000,
                'status' => true,
            ],
        ];

        foreach ($variants as $variant) {
            ProductVariant::updateOrCreate(
                [
                    'product_id' => $iphone->id,
                    'color' => $variant['color'],
                    'storage' => $variant['storage'],
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

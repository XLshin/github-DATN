<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        ProductVariant::query()->delete();

        $products = Product::query()->get();

        foreach ($products as $product) {
            $storage = $product->storage ?: '1 unit';
            $variantDefinitions = [
                [
                    'color' => 'Đen',
                    'storage' => $storage,
                    'stock_quantity' => max(5, (int) ceil($product->stock_quantity / 2)),
                    'additional_price' => 0,
                    'status' => true,
                ],
                [
                    'color' => 'Trắng',
                    'storage' => $storage,
                    'stock_quantity' => max(3, (int) floor($product->stock_quantity / 3)),
                    'additional_price' => 500000,
                    'status' => true,
                ],
            ];

            if ((int) $product->price >= 15000000) {
                $variantDefinitions[] = [
                    'color' => 'Bạc',
                    'storage' => $storage,
                    'stock_quantity' => max(2, (int) floor($product->stock_quantity / 4)),
                    'additional_price' => 1500000,
                    'status' => true,
                ];
            }

            foreach ($variantDefinitions as $variantData) {
                ProductVariant::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'color' => $variantData['color'],
                    ],
                    [
                        'image_path' => null,
                        'stock_quantity' => $variantData['stock_quantity'],
                        'additional_price' => $variantData['additional_price'],
                        'status' => $variantData['status'],
                    ]
                );
            }
        }
    }
}

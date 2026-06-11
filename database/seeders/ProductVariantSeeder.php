<?php

namespace Database\Seeders;

use App\Models\ProductVariant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductVariant::insert([

            [
                'product_id' => 1,
                'color' => 'Titan Đen',
                'storage' => '128GB',
                'stock_quantity' => 10,
                'additional_price' => 0,
                'status' => true
            ],

            [
                'product_id' => 1,
                'color' => 'Titan Đen',
                'storage' => '256GB',
                'stock_quantity' => 8,
                'additional_price' => 3000000,
                'status' => true
            ],

            [
                'product_id' => 1,
                'color' => 'Titan Trắng',
                'storage' => '128GB',
                'stock_quantity' => 15,
                'additional_price' => 0,
                'status' => true
            ],

            [
                'product_id' => 1,
                'color' => 'Titan Trắng',
                'storage' => '256GB',
                'stock_quantity' => 12,
                'additional_price' => 3000000,
                'status' => true
            ],

            [
                'product_id' => 1,
                'color' => 'Titan Sa Mạc',
                'storage' => '128GB',
                'stock_quantity' => 5,
                'additional_price' => 0,
                'status' => true
            ],

            [
                'product_id' => 1,
                'color' => 'Titan Sa Mạc',
                'storage' => '256GB',
                'stock_quantity' => 3,
                'additional_price' => 3000000,
                'status' => true
            ],

        ]);
    }
}

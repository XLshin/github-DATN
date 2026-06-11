<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::insert([

            [

                'category_id' => 1,

                'brand_id' => 1,

                'name' => 'iPhone 16 Pro',

                'slug' => 'iphone-16-pro',

                'description' => 'iPhone 16 Pro chính hãng',

                'price' => 29990000,

                'stock_quantity' => 50,

                'status' => true

            ],

            [

                'category_id' => 1,

                'brand_id' => 2,

                'name' => 'Samsung Galaxy S25',

                'slug' => 'samsung-galaxy-s25',

                'description' => 'Samsung Galaxy S25 chính hãng',

                'price' => 24990000,

                'stock_quantity' => 30,

                'status' => true

            ]

        ]);
    }
}

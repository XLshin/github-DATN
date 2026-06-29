<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $products = DB::table('products')->get();

        foreach ($products as $product) {
            // Xóa ảnh cũ để tránh trùng
            DB::table('product_images')->where('product_id', $product->id)->delete();

            DB::table('product_images')->insert([
                [
                    'product_id' => $product->id,
                    'image_path' => 'products/' . $product->slug . '-1.jpg',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'product_id' => $product->id,
                    'image_path' => 'products/' . $product->slug . '-2.jpg',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'product_id' => $product->id,
                    'image_path' => 'products/' . $product->slug . '-3.jpg',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }
}

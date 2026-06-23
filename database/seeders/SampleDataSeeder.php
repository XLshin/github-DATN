<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Tạo danh mục mẫu
        |--------------------------------------------------------------------------
        */
        $categories = [];

        for ($i = 1; $i <= 3; $i++) {
            $categories[] = Category::updateOrCreate(
                ['name' => 'Category ' . $i],
                [
                    'description' => 'Sample category ' . $i,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Tạo thương hiệu mẫu
        |--------------------------------------------------------------------------
        */
        $brands = [];

        for ($i = 1; $i <= 3; $i++) {
            $brands[] = Brand::updateOrCreate(
                ['name' => 'Brand ' . $i],
                [
                    'logo' => null,
                    'description' => 'Sample brand ' . $i,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Tạo sản phẩm mẫu
        |--------------------------------------------------------------------------
        */
        for ($i = 1; $i <= 12; $i++) {
            $name = 'Sample Product ' . $i;
            $slug = Str::slug($name);

            Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $categories[array_rand($categories)]->id,
                    'brand_id' => $brands[array_rand($brands)]->id,
                    'name' => $name,
                    'description' => 'This is the description for sample product ' . $i,
                    'price' => rand(100000, 2000000),
                    'stock_quantity' => rand(1, 50),
                    'thumbnail' => null,
                    'status' => true,
                ]
            );
        }
    }
}

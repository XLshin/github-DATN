<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        // create 3 categories
        $categories = [];
        for ($i = 1; $i <= 3; $i++) {
            $categories[] = Category::create([
                'name' => 'Category '.$i,
                'description' => 'Sample category '.$i
            ]);
        }

        // create 3 brands
        $brands = [];
        for ($i = 1; $i <= 3; $i++) {
            $brands[] = Brand::create([
                'name' => 'Brand '.$i,
                'logo' => null,
                'description' => 'Sample brand '.$i
            ]);
        }

        // create products
        for ($i = 1; $i <= 12; $i++) {
            Product::create([
                'category_id' => $categories[array_rand($categories)]->id,
                'brand_id' => $brands[array_rand($brands)]->id,
                'name' => 'Sample Product '.$i,
                'slug' => 'sample-product-'.$i,
                'description' => 'This is the description for sample product '.$i,
                'price' => rand(100000, 2000000),
                'stock_quantity' => rand(1, 50),
                'thumbnail' => null,
                'status' => true,
            ]);
        }
    }
}

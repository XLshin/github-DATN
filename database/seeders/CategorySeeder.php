<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Điện thoại', 'description' => 'Danh mục điện thoại'],
            ['name' => 'Máy tính bảng', 'description' => 'Danh mục máy tính bảng'],
            ['name' => 'Phụ kiện', 'description' => 'Danh mục phụ kiện'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class Catalog500Seeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Điện thoại' => [
                'brands' => ['Apple', 'Samsung', 'Xiaomi', 'OPPO', 'vivo', 'realme', 'Google', 'Nothing', 'HONOR', 'Nokia'],
                'models' => ['iPhone', 'Galaxy S', 'Redmi Note', 'Reno', 'V', 'GT Neo', 'Pixel', 'Phone', 'Magic', 'G'],
                'prices' => [3990000, 8990000, 14990000, 22990000, 32990000],
                'storages' => ['128GB', '256GB', '512GB'],
            ],
            'Máy tính bảng' => [
                'brands' => ['Apple', 'Samsung', 'Xiaomi', 'Lenovo', 'Huawei', 'HONOR'],
                'models' => ['iPad Air', 'Galaxy Tab S', 'Pad', 'Tab P', 'MatePad', 'Pad X'],
                'prices' => [5490000, 8990000, 12990000, 18990000, 24990000],
                'storages' => ['64GB', '128GB', '256GB'],
            ],
            'Đồng Hồ' => [
                'brands' => ['Apple', 'Samsung', 'Xiaomi', 'Garmin', 'Huawei', 'Amazfit'],
                'models' => ['Watch Series', 'Galaxy Watch', 'Watch S', 'Forerunner', 'Watch GT', 'GTR'],
                'prices' => [990000, 1990000, 3990000, 6990000, 10990000],
                'storages' => ['GPS', 'Bluetooth', 'LTE'],
            ],
            'Phụ kiện' => [
                'brands' => ['Apple', 'Samsung', 'Anker', 'Baseus', 'UGREEN', 'Xiaomi', 'Belkin', 'JBL'],
                'models' => ['AirPods', 'Galaxy Buds', 'Sạc nhanh', 'Cáp USB-C', 'Pin dự phòng', 'Ốp lưng', 'Tai nghe Bluetooth', 'Giá đỡ điện thoại'],
                'prices' => [149000, 299000, 499000, 890000, 1590000],
                'storages' => ['Tiêu chuẩn', '20W', '45W', '65W'],
            ],
        ];

        $colors = ['Đen', 'Trắng', 'Xanh dương', 'Bạc', 'Tím', 'Hồng', 'Titan', 'Xám'];
        $counter = 1;

        foreach ($catalog as $categoryName => $data) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['description' => 'Danh mục ' . $categoryName]
            );

            foreach ($data['brands'] as $brandName) {
                $brand = Brand::firstOrCreate(['name' => $brandName]);
                $brand->categories()->syncWithoutDetaching([$category->id]);
            }

            for ($index = 0; $index < 125; $index++, $counter++) {
                $brandName = $data['brands'][$index % count($data['brands'])];
                $model = $data['models'][$index % count($data['models'])];
                $brand = Brand::where('name', $brandName)->firstOrFail();
                $series = 10 + intdiv($index, count($data['models']));
                $name = $brandName . ' ' . $model . ' ' . $series;
                $slug = 'demo-catalog-500-' . $counter;
                $price = $data['prices'][$index % count($data['prices'])];
                $storage = $data['storages'][$index % count($data['storages'])];

                $group = ProductGroup::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $category->id,
                        'brand_id' => $brand->id,
                        'name' => $name,
                        'description' => $name . ' chính hãng, bảo hành đầy đủ, phù hợp cho nhu cầu học tập, làm việc và giải trí.',
                        'status' => true,
                        'product_type' => 'quantity',
                    ]
                );

                $product = Product::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'product_group_id' => $group->id,
                        'category_id' => $category->id,
                        'brand_id' => $brand->id,
                        'name' => $name . ' ' . $storage,
                        'storage' => $storage,
                        'description' => $group->description,
                        'price' => $price,
                        'thumbnail' => null,
                        'status' => true,
                        'product_type' => 'quantity',
                    ]
                );

                ProductVariant::updateOrCreate(
                    ['product_id' => $product->id, 'color' => $colors[$index % count($colors)]],
                    [
                        'stock_quantity' => 10 + ($index % 41),
                        'additional_price' => 0,
                        'status' => true,
                    ]
                );
            }
        }
    }
}

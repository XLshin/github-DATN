<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class TestCartProductSeeder extends Seeder
{
    /**
     * Sản phẩm dùng để test chức năng giỏ hàng: chọn biến thể, thêm/xóa/cập nhật số lượng,
     * chọn thanh toán một phần giỏ hàng và đặt hộ.
     */
    public function run(): void
    {
        $category = Category::where('name', 'Điện thoại')->first();
        $accessoryCategory = Category::where('name', 'Phụ kiện')->first();
        $brand = Brand::where('name', 'Apple')->first();

        if (! $category || ! $accessoryCategory || ! $brand) {
            throw new \Exception('Thiếu danh mục/thương hiệu. Hãy chạy CategorySeeder và BrandSeeder trước.');
        }

        $products = [
            [
                'slug' => 'test-cart-phone-quantity',
                'name' => '[TEST] Điện thoại Cart Demo (theo số lượng)',
                'storage' => '128GB',
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'description' => 'Sản phẩm dùng để test giỏ hàng: quản lý tồn kho theo số lượng, có nhiều biến thể màu.',
                'price' => 9990000,
                'stock_quantity' => 100,
                'status' => true,
                'product_type' => 'quantity',
                'variants' => [
                    ['color' => 'Đen Test', 'stock_quantity' => 30, 'additional_price' => 0],
                    ['color' => 'Trắng Test', 'stock_quantity' => 25, 'additional_price' => 200000],
                    ['color' => 'Xanh Test', 'stock_quantity' => 20, 'additional_price' => 500000],
                ],
            ],
            [
                'slug' => 'test-cart-phone-imei',
                'name' => '[TEST] Điện thoại Cart Demo (theo IMEI)',
                'storage' => '256GB',
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'description' => 'Sản phẩm dùng để test giỏ hàng: quản lý tồn kho theo IMEI, có nhiều biến thể màu.',
                'price' => 15990000,
                'stock_quantity' => 50,
                'status' => true,
                'product_type' => 'imei/serial',
                'variants' => [
                    ['color' => 'Titan Test', 'stock_quantity' => 10, 'additional_price' => 0],
                    ['color' => 'Bạc Test', 'stock_quantity' => 10, 'additional_price' => 1000000],
                ],
            ],
            [
                'slug' => 'test-cart-accessory',
                'name' => '[TEST] Phụ kiện Cart Demo',
                'storage' => '1 unit',
                'category_id' => $accessoryCategory->id,
                'brand_id' => $brand->id,
                'description' => 'Phụ kiện test giỏ hàng với biến thể màu và giá cộng thêm.',
                'price' => 590000,
                'stock_quantity' => 200,
                'status' => true,
                'product_type' => 'quantity',
                'variants' => [
                    ['color' => 'Đen Test', 'stock_quantity' => 100, 'additional_price' => 0],
                    ['color' => 'Hồng Test', 'stock_quantity' => 100, 'additional_price' => 50000],
                ],
            ],
        ];

        foreach ($products as $data) {
            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $data['category_id'],
                    'brand_id' => $data['brand_id'],
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'stock_quantity' => $data['stock_quantity'],
                    'storage' => $data['storage'],
                    'thumbnail' => null,
                    'status' => $data['status'],
                    'product_type' => $data['product_type'],
                ]
            );

            foreach ($data['variants'] as $variant) {
                ProductVariant::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'color' => $variant['color'],
                    ],
                    [
                        'stock_quantity' => $variant['stock_quantity'],
                        'additional_price' => $variant['additional_price'],
                        'status' => true,
                    ]
                );
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryNames = ['Điện thoại', 'Máy tính bảng', 'Phụ kiện'];
        $brandNames = ['Apple', 'Samsung', 'Xiaomi', 'Oppo'];

        $categories = Category::whereIn('name', $categoryNames)->get()->keyBy('name');
        $brands = Brand::whereIn('name', $brandNames)->get()->keyBy('name');

        $missingCategories = array_diff($categoryNames, $categories->keys()->toArray());
        $missingBrands = array_diff($brandNames, $brands->keys()->toArray());

        if (!empty($missingCategories)) {
            throw new \Exception('Thiếu danh mục: ' . implode(', ', $missingCategories) . '. Hãy chạy CategorySeeder trước.');
        }

        if (!empty($missingBrands)) {
            throw new \Exception('Thiếu thương hiệu: ' . implode(', ', $missingBrands) . '. Hãy chạy BrandSeeder trước.');
        }

        Product::query()->delete();
        ProductGroup::query()->delete();

        $productTemplates = [
            ['category' => 'Điện thoại', 'brand' => 'Apple', 'name' => 'iPhone 16', 'storage' => '128GB', 'price' => 19990000, 'stock_quantity' => 30, 'product_type' => 'imei/serial'],
            ['category' => 'Điện thoại', 'brand' => 'Apple', 'name' => 'iPhone 16 Plus', 'storage' => '256GB', 'price' => 22990000, 'stock_quantity' => 26, 'product_type' => 'imei/serial'],
            ['category' => 'Điện thoại', 'brand' => 'Apple', 'name' => 'iPhone 16 Pro', 'storage' => '256GB', 'price' => 29990000, 'stock_quantity' => 24, 'product_type' => 'imei/serial'],
            ['category' => 'Điện thoại', 'brand' => 'Apple', 'name' => 'iPhone 16 Pro Max', 'storage' => '512GB', 'price' => 34990000, 'stock_quantity' => 20, 'product_type' => 'imei/serial'],
            ['category' => 'Điện thoại', 'brand' => 'Apple', 'name' => 'iPhone 15', 'storage' => '128GB', 'price' => 21990000, 'stock_quantity' => 22, 'product_type' => 'imei/serial'],
            ['category' => 'Điện thoại', 'brand' => 'Apple', 'name' => 'iPhone 15 Plus', 'storage' => '256GB', 'price' => 24990000, 'stock_quantity' => 18, 'product_type' => 'imei/serial'],
            ['category' => 'Điện thoại', 'brand' => 'Samsung', 'name' => 'Samsung Galaxy S25', 'storage' => '256GB', 'price' => 24990000, 'stock_quantity' => 25, 'product_type' => 'imei/serial'],
            ['category' => 'Điện thoại', 'brand' => 'Samsung', 'name' => 'Samsung Galaxy S25 Ultra', 'storage' => '512GB', 'price' => 32990000, 'stock_quantity' => 21, 'product_type' => 'imei/serial'],
            ['category' => 'Điện thoại', 'brand' => 'Samsung', 'name' => 'Samsung Galaxy Z Flip 6', 'storage' => '256GB', 'price' => 27990000, 'stock_quantity' => 17, 'product_type' => 'imei/serial'],
            ['category' => 'Điện thoại', 'brand' => 'Samsung', 'name' => 'Samsung Galaxy A56', 'storage' => '128GB', 'price' => 11990000, 'stock_quantity' => 35, 'product_type' => 'quantity'],
            ['category' => 'Điện thoại', 'brand' => 'Samsung', 'name' => 'Samsung Galaxy A36', 'storage' => '128GB', 'price' => 9990000, 'stock_quantity' => 31, 'product_type' => 'quantity'],
            ['category' => 'Điện thoại', 'brand' => 'Xiaomi', 'name' => 'Xiaomi 14', 'storage' => '256GB', 'price' => 17990000, 'stock_quantity' => 28, 'product_type' => 'imei/serial'],
            ['category' => 'Điện thoại', 'brand' => 'Xiaomi', 'name' => 'Xiaomi 14 Ultra', 'storage' => '512GB', 'price' => 23990000, 'stock_quantity' => 19, 'product_type' => 'imei/serial'],
            ['category' => 'Điện thoại', 'brand' => 'Xiaomi', 'name' => 'Xiaomi Redmi Note 15', 'storage' => '128GB', 'price' => 6990000, 'stock_quantity' => 40, 'product_type' => 'quantity'],
            ['category' => 'Điện thoại', 'brand' => 'Xiaomi', 'name' => 'Xiaomi Redmi A4', 'storage' => '64GB', 'price' => 3490000, 'stock_quantity' => 45, 'product_type' => 'quantity'],
            ['category' => 'Điện thoại', 'brand' => 'Xiaomi', 'name' => 'Xiaomi Redmi 13C', 'storage' => '128GB', 'price' => 4290000, 'stock_quantity' => 38, 'product_type' => 'quantity'],
            ['category' => 'Điện thoại', 'brand' => 'Oppo', 'name' => 'Oppo Reno 12', 'storage' => '128GB', 'price' => 8990000, 'stock_quantity' => 32, 'product_type' => 'quantity'],
            ['category' => 'Điện thoại', 'brand' => 'Oppo', 'name' => 'Oppo Find X8', 'storage' => '256GB', 'price' => 19990000, 'stock_quantity' => 24, 'product_type' => 'imei/serial'],
            ['category' => 'Điện thoại', 'brand' => 'Oppo', 'name' => 'Oppo A3', 'storage' => '128GB', 'price' => 6490000, 'stock_quantity' => 36, 'product_type' => 'quantity'],
            ['category' => 'Điện thoại', 'brand' => 'Oppo', 'name' => 'Oppo A5', 'storage' => '128GB', 'price' => 5890000, 'stock_quantity' => 33, 'product_type' => 'quantity'],
            ['category' => 'Máy tính bảng', 'brand' => 'Apple', 'name' => 'iPad Air 6', 'storage' => '256GB', 'price' => 22990000, 'stock_quantity' => 20, 'product_type' => 'quantity'],
            ['category' => 'Máy tính bảng', 'brand' => 'Apple', 'name' => 'iPad Pro 11', 'storage' => '512GB', 'price' => 29990000, 'stock_quantity' => 15, 'product_type' => 'quantity'],
            ['category' => 'Máy tính bảng', 'brand' => 'Apple', 'name' => 'iPad mini 7', 'storage' => '128GB', 'price' => 16990000, 'stock_quantity' => 17, 'product_type' => 'quantity'],
            ['category' => 'Máy tính bảng', 'brand' => 'Apple', 'name' => 'iPad 10', 'storage' => '64GB', 'price' => 12990000, 'stock_quantity' => 22, 'product_type' => 'quantity'],
            ['category' => 'Máy tính bảng', 'brand' => 'Samsung', 'name' => 'Samsung Galaxy Tab S10', 'storage' => '128GB', 'price' => 19990000, 'stock_quantity' => 18, 'product_type' => 'quantity'],
            ['category' => 'Máy tính bảng', 'brand' => 'Samsung', 'name' => 'Samsung Galaxy Tab S9 FE', 'storage' => '256GB', 'price' => 12990000, 'stock_quantity' => 21, 'product_type' => 'quantity'],
            ['category' => 'Máy tính bảng', 'brand' => 'Samsung', 'name' => 'Samsung Galaxy Tab A9', 'storage' => '64GB', 'price' => 6990000, 'stock_quantity' => 28, 'product_type' => 'quantity'],
            ['category' => 'Máy tính bảng', 'brand' => 'Samsung', 'name' => 'Samsung Galaxy Tab A9+', 'storage' => '128GB', 'price' => 8990000, 'stock_quantity' => 25, 'product_type' => 'quantity'],
            ['category' => 'Máy tính bảng', 'brand' => 'Xiaomi', 'name' => 'Xiaomi Pad 7 Pro', 'storage' => '256GB', 'price' => 13990000, 'stock_quantity' => 24, 'product_type' => 'quantity'],
            ['category' => 'Máy tính bảng', 'brand' => 'Xiaomi', 'name' => 'Xiaomi Pad 6', 'storage' => '128GB', 'price' => 9990000, 'stock_quantity' => 19, 'product_type' => 'quantity'],
            ['category' => 'Máy tính bảng', 'brand' => 'Xiaomi', 'name' => 'Xiaomi Pad 5', 'storage' => '128GB', 'price' => 7990000, 'stock_quantity' => 23, 'product_type' => 'quantity'],
            ['category' => 'Máy tính bảng', 'brand' => 'Oppo', 'name' => 'Oppo Pad Air', 'storage' => '128GB', 'price' => 8990000, 'stock_quantity' => 16, 'product_type' => 'quantity'],
            ['category' => 'Máy tính bảng', 'brand' => 'Oppo', 'name' => 'Oppo Pad 2', 'storage' => '256GB', 'price' => 10990000, 'stock_quantity' => 13, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Apple', 'name' => 'Apple AirPods Pro 3', 'storage' => '1 unit', 'price' => 5990000, 'stock_quantity' => 45, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Apple', 'name' => 'Apple Watch Series 10', 'storage' => '1 unit', 'price' => 10990000, 'stock_quantity' => 22, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Apple', 'name' => 'Apple Magic Keyboard', 'storage' => '1 unit', 'price' => 4990000, 'stock_quantity' => 18, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Apple', 'name' => 'Apple Pencil Pro', 'storage' => '1 unit', 'price' => 3990000, 'stock_quantity' => 16, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Samsung', 'name' => 'Samsung Galaxy Buds 3', 'storage' => '1 unit', 'price' => 2490000, 'stock_quantity' => 38, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Samsung', 'name' => 'Samsung 45W Charger', 'storage' => '45W', 'price' => 690000, 'stock_quantity' => 57, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Samsung', 'name' => 'Samsung SmartTag', 'storage' => '1 unit', 'price' => 790000, 'stock_quantity' => 41, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Xiaomi', 'name' => 'Xiaomi 65W Charger', 'storage' => '65W', 'price' => 490000, 'stock_quantity' => 60, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Xiaomi', 'name' => 'Xiaomi Buds 5', 'storage' => '1 unit', 'price' => 1590000, 'stock_quantity' => 36, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Xiaomi', 'name' => 'Xiaomi Power Bank 10000', 'storage' => '10000mAh', 'price' => 690000, 'stock_quantity' => 51, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Oppo', 'name' => 'Oppo Enco Air4', 'storage' => '1 unit', 'price' => 1290000, 'stock_quantity' => 33, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Oppo', 'name' => 'Oppo 45W Charger', 'storage' => '45W', 'price' => 590000, 'stock_quantity' => 39, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Oppo', 'name' => 'Oppo Wireless Earbuds', 'storage' => '1 unit', 'price' => 1490000, 'stock_quantity' => 30, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Oppo', 'name' => 'Oppo Type-C Cable', 'storage' => '1 unit', 'price' => 290000, 'stock_quantity' => 48, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Apple', 'name' => 'Apple USB-C Hub', 'storage' => '1 unit', 'price' => 2490000, 'stock_quantity' => 14, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Samsung', 'name' => 'Samsung USB-C Hub', 'storage' => '1 unit', 'price' => 1590000, 'stock_quantity' => 12, 'product_type' => 'quantity'],
            ['category' => 'Phụ kiện', 'brand' => 'Xiaomi', 'name' => 'Xiaomi USB-C Hub', 'storage' => '1 unit', 'price' => 890000, 'stock_quantity' => 15, 'product_type' => 'quantity'],
        ];

        $products = [];
        foreach ($productTemplates as $index => $template) {
            $products[] = [
                'slug' => Str::slug($template['name']) . '-' . ($index + 1),
                'name' => $template['name'],
                'category' => $template['category'],
                'brand' => $template['brand'],
                'storage' => $template['storage'],
                'description' => $template['name'] . ' là sản phẩm chính hãng, phù hợp cho nhu cầu sử dụng hàng ngày.',
                'price' => $template['price'],
                'stock_quantity' => $template['stock_quantity'],
                'status' => true,
                'product_type' => $template['product_type'],
            ];
        }

        foreach ($products as $productData) {
            $productGroup = ProductGroup::updateOrCreate(
                ['slug' => Str::slug($productData['name'])],
                [
                    'category_id' => $categories[$productData['category']]->id,
                    'brand_id' => $brands[$productData['brand']]->id,
                    'name' => $productData['name'],
                    'slug' => Str::slug($productData['name']),
                    'description' => $productData['description'],
                    'status' => $productData['status'],
                    'product_type' => $productData['product_type'],
                ]
            );

            $product = Product::updateOrCreate(
                ['slug' => $productData['slug']],
                [
                    'category_id' => $categories[$productData['category']]->id,
                    'brand_id' => $brands[$productData['brand']]->id,
                    'product_group_id' => $productGroup->id,
                    'name' => $productData['name'],
                    'storage' => $productData['storage'] ?? null,
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'stock_quantity' => $productData['stock_quantity'],
                    'thumbnail' => null,
                    'status' => $productData['status'],
                    'product_type' => $productData['product_type'],
                ]
            );

            $this->seedVariantsForProduct($product);
        }
    }

    private function seedVariantsForProduct(Product $product): void
    {
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

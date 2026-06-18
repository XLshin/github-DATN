<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\PointHistory;
use App\Models\Coupon;
use App\Models\Banner;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductImage;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. TẠO TÀI KHOẢN USER TEST
        $user = User::firstOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Nguyễn Văn A',
                'password' => Hash::make('123456'),
                'points' => 150, // Cấp sẵn 150 điểm để test
            ]
        );

        // Tài khoản admin để vào trang Dashboard, quản lý review
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Quản Trị Viên',
                'password' => Hash::make('123456'),
                // Thêm trường role nếu db của bạn có phân quyền, ví dụ: 'role' => 'admin'
            ]
        );

        // 2. TẠO DANH MỤC (CATEGORIES) - BỎ SLUG
        $iphone = Category::firstOrCreate(['name' => 'iPhone']);
        $samsung = Category::firstOrCreate(['name' => 'Samsung']);

        // 3. TẠO THƯƠNG HIỆU (BRANDS) - BỎ SLUG
        $appleBrand = Brand::firstOrCreate(['name' => 'Apple']);
        $samsungBrand = Brand::firstOrCreate(['name' => 'Samsung']);

       // 4. TẠO SẢN PHẨM (PRODUCTS) - THÊM DESCRIPTION
        $product1 = Product::updateOrCreate(
            ['slug' => 'iphone-15-pro-max'],
            [
                'category_id' => $iphone->id,
                'brand_id' => $appleBrand->id,
                'name' => 'iPhone 15 Pro Max',
                'description' => 'Điện thoại iPhone thế hệ mới vỏ Titan siêu bền.',
                'price' => 29990000,
                'stock_quantity' => 20,
                'thumbnail' => 'products/iphone-15-pro-max.jpg',
                'status' => true,
            ]
        );

        $product2 = Product::updateOrCreate(
            ['slug' => 'samsung-galaxy-s24-ultra'],
            [
                'category_id' => $samsung->id,
                'brand_id' => $samsungBrand->id,
                'name' => 'Samsung Galaxy S24 Ultra',
                'description' => 'Flagship đỉnh cao tích hợp quyền năng Galaxy AI.',
                'price' => 27990000,
                'stock_quantity' => 15,
                'thumbnail' => 'products/samsung-galaxy-s24-ultra.jpg',
                'status' => true,
            ]
        );


        // 5. TẠO ĐÁNH GIÁ MẪU (REVIEWS) - ĐỔI STATUS THÀNH SỐ
        Review::create([
            'product_id' => $product1->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'Máy dùng rất mượt, pin trâu và camera chụp đêm xuất sắc!',
            'status' => 1, // Thay 'approved' bằng số 1 (Đã duyệt)
        ]);

        Review::create([
            'product_id' => $product1->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => 'Sản phẩm tốt nhưng giao hàng hơi chậm một chút.',
            'status' => 0, // Thay 'pending' bằng số 0 (Chờ duyệt)
        ]);

        // 6. TẠO LỊCH SỬ TÍCH ĐIỂM (POINT HISTORIES)
        PointHistory::create([
            'user_id' => $user->id,
            'points' => 100,
            'type' => 'earn', // Nhận điểm
            'description' => 'Tích điểm từ đơn hàng #HD10023',
        ]);

        PointHistory::create([
            'user_id' => $user->id,
            'points' => 50,
            'type' => 'earn',
            'description' => 'Thưởng điểm khi để lại đánh giá sản phẩm',
        ]);

        // 7. TẠO HÌNH ẢNH SẢN PHẨM MẪU
        ProductImage::create([
            'product_id' => $product1->id,
            'image_path' => 'products/iphone-15-pro-max-1.jpg',
        ]);

        ProductImage::create([
            'product_id' => $product2->id,
            'image_path' => 'products/samsung-galaxy-s24-ultra-1.jpg',
        ]);

        // 8. TẠO BANNER VÀ COUPON MẪU
        Banner::create([
            'title' => 'Flash sale cuối tuần',
            'image' => 'banners/flash-sale.jpg',
            'link' => '/sale',
            'status' => true,
        ]);

        Coupon::create([
            'code' => 'SUMMER20',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'min_order_amount' => 10000000,
            'usage_limit' => 50,
            'used_count' => 0,
            'start_date' => now(),
            'end_date' => now()->addWeeks(4),
            'status' => true,
        ]);

        // 9. TẠO ĐƠN HÀNG VÀ CHI TIẾT ĐƠN HÀNG MẪU
        $order1 = Order::create([
            'user_id' => $user->id,
            'order_code' => 'HD10001',
            'customer_name' => $user->name,
            'customer_phone' => '0961234567',
            'shipping_address' => '123 Đường ABC, Quận 1, TP.HCM',
            'subtotal' => 29990000,
            'membership_discount' => 0,
            'coupon_discount' => 0,
            'total_amount' => 29990000,
            'status' => 'completed',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product1->id,
            'price' => 29990000,
            'quantity' => 1,
            'total' => 29990000,
        ]);

        $order2 = Order::create([
            'user_id' => $user->id,
            'order_code' => 'HD10002',
            'customer_name' => $user->name,
            'customer_phone' => '0961234567',
            'shipping_address' => '123 Đường ABC, Quận 1, TP.HCM',
            'subtotal' => 27990000,
            'membership_discount' => 0,
            'coupon_discount' => 0,
            'total_amount' => 27990000,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $product2->id,
            'price' => 27990000,
            'quantity' => 1,
            'total' => 27990000,
        ]);

        // 10. TẠO GIỎ HÀNG MẪU
        $cart = Cart::create([
            'user_id' => $user->id,
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);
    }
}

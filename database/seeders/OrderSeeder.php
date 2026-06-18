<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | 1. Tạo customer test nếu chưa có
        |--------------------------------------------------------------------------
        */
        DB::table('users')->updateOrInsert(
            ['email' => 'customer.test@gmail.com'],
            [
                'name' => 'Khách hàng test',
                'phone' => '0987654321',
                'address' => 'Số 1 Cầu Giấy, Hà Nội',
                'password' => Hash::make('12345678'),
                'role' => 'customer',
                'total_spent' => 0,
                'membership_level' => 'bronze',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $user = DB::table('users')
            ->where('email', 'customer.test@gmail.com')
            ->first();

        if (! $user) {
            throw new \Exception('Không tạo được user test.');
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Lấy 1 biến thể sản phẩm có sẵn
        |--------------------------------------------------------------------------
        | Seeder của bạn đã có ProductSeeder và ProductVariantSeeder,
        | nên ở đây chỉ lấy dữ liệu đã có, không tạo lại sản phẩm.
        */
        $variant = DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->select(
                'product_variants.id as product_variant_id',
                'product_variants.product_id',
                'products.price as product_price',
                'product_variants.additional_price'
            )
            ->where('products.status', true)
            ->where('product_variants.status', true)
            ->first();

        if (! $variant) {
            throw new \Exception('Chưa có product_variants. Hãy chạy ProductSeeder và ProductVariantSeeder trước.');
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Tính tiền đơn hàng
        |--------------------------------------------------------------------------
        */
        $price = (float) $variant->product_price + (float) $variant->additional_price;
        $quantity = 1;
        $subtotal = $price * $quantity;
        $membershipDiscount = 0;
        $couponDiscount = 0;
        $totalAmount = $subtotal - $membershipDiscount - $couponDiscount;

        /*
        |--------------------------------------------------------------------------
        | 4. Tạo đơn hàng test trạng thái processing
        |--------------------------------------------------------------------------
        | Trạng thái processing dùng để test nút/tính năng tạo vận đơn.
        */
        DB::table('orders')->updateOrInsert(
            ['order_code' => 'ORD_TEST_SHIPMENT_001'],
            [
                'user_id' => $user->id,
                'customer_name' => 'Khách hàng test',
                'customer_phone' => '0987654321',
                'shipping_address' => 'Số 1 Cầu Giấy, Hà Nội',
                'subtotal' => $subtotal,
                'membership_discount' => $membershipDiscount,
                'coupon_discount' => $couponDiscount,
                'total_amount' => $totalAmount,
                'status' => 'processing',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $order = DB::table('orders')
            ->where('order_code', 'ORD_TEST_SHIPMENT_001')
            ->first();

        if (! $order) {
            throw new \Exception('Không tạo được đơn hàng test.');
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Xóa dữ liệu con cũ để chạy lại seeder không bị trùng
        |--------------------------------------------------------------------------
        */
        DB::table('shipments')
            ->where('order_id', $order->id)
            ->delete();

        DB::table('payments')
            ->where('order_id', $order->id)
            ->delete();

        DB::table('order_items')
            ->where('order_id', $order->id)
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | 6. Tạo chi tiết đơn hàng
        |--------------------------------------------------------------------------
        */
        DB::table('order_items')->insert([
            'order_id' => $order->id,
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->product_variant_id,
            'price' => $price,
            'quantity' => $quantity,
            'total' => $subtotal,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 7. Tạo thanh toán COD mẫu
        |--------------------------------------------------------------------------
        */
        DB::table('payments')->insert([
            'order_id' => $order->id,
            'payment_method' => 'cod',
            'amount' => $totalAmount,
            'payment_status' => 'pending',
            'transaction_code' => null,
            'paid_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

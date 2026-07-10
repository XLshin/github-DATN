<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();

            /*
            |--------------------------------------------------------------------------
            | 1. Tạo customer test nếu chưa có
            |--------------------------------------------------------------------------
            */
            $userData = [
                'name' => 'Khách hàng test',
                'phone' => '0987654321',
                'address' => 'Số 1 Cầu Giấy, Hà Nội',
                'password' => Hash::make('12345678'),
                'role' => 'customer',
                'total_spent' => 0,
                'membership_level' => 'bronze',
                'updated_at' => $now,
            ];

            if (DB::table('users')->where('email', 'customer.test@gmail.com')->exists()) {
                DB::table('users')
                    ->where('email', 'customer.test@gmail.com')
                    ->update($userData);
            } else {
                $userData['email'] = 'customer.test@gmail.com';
                $userData['email_verified_at'] = $now;
                $userData['points'] = 0;
                $userData['remember_token'] = null;
                $userData['created_at'] = $now;

                DB::table('users')->insert($userData);
            }

            $user = DB::table('users')
                ->where('email', 'customer.test@gmail.com')
                ->first();

            if (! $user) {
                throw new \Exception('Không tạo được user test.');
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Lấy 1 biến thể sản phẩm quản lý theo IMEI
            |--------------------------------------------------------------------------
            | Flow mới:
            | - Tạo đơn hàng trước.
            | - order_items.imei_id để null.
            | - Khi admin đóng gói mới chọn IMEI.
            */
            $variant = DB::table('product_variants')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->select(
                    'product_variants.id as product_variant_id',
                    'product_variants.product_id',
                    'products.price as product_price',
                    'products.product_type',
                    'product_variants.additional_price'
                )
                ->where('products.status', true)
                ->where('product_variants.status', true)
                ->where('products.product_type', 'imei/serial')
                ->first();

            if (! $variant) {
                throw new \Exception('Chưa có biến thể sản phẩm IMEI hợp lệ. Hãy chạy ProductSeeder, ProductVariantSeeder và ImeiSeeder trước OrderSeeder.');
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Tính tiền đơn hàng
            |--------------------------------------------------------------------------
            */
            $price = (float) $variant->product_price + (float) ($variant->additional_price ?? 0);
            $quantity = 1;
            $subtotal = $price * $quantity;

            $membershipDiscount = 0;
            $couponDiscount = 0;
            $totalAmount = $subtotal - $membershipDiscount - $couponDiscount;

            /*
            |--------------------------------------------------------------------------
            | 4. Tạo hoặc cập nhật đơn hàng test
            |--------------------------------------------------------------------------
            */
            $orderData = [
                'user_id' => $user->id,
                'customer_name' => 'Khách hàng test',
                'customer_phone' => '0987654321',
                'shipping_address' => 'Số 1 Cầu Giấy, Hà Nội',
                'subtotal' => $subtotal,
                'membership_discount' => $membershipDiscount,
                'coupon_discount' => $couponDiscount,
                'total_amount' => $totalAmount,
                'status' => 'processing',
                'fulfillment_status' => 'waiting_pack',
                'confirmed_at' => $now,
                'packed_at' => null,
                'handed_over_at' => null,
                'delivered_at' => null,
                'cancelled_at' => null,
                'shipping_label_printed_at' => null,
                'updated_at' => $now,
            ];

            if (DB::table('orders')->where('order_code', 'ORD_TEST_SHIPMENT_001')->exists()) {
                DB::table('orders')
                    ->where('order_code', 'ORD_TEST_SHIPMENT_001')
                    ->update($orderData);
            } else {
                $orderData['order_code'] = 'ORD_TEST_SHIPMENT_001';
                $orderData['created_at'] = $now;

                DB::table('orders')->insert($orderData);
            }

            $order = DB::table('orders')
                ->where('order_code', 'ORD_TEST_SHIPMENT_001')
                ->first();

            if (! $order) {
                throw new \Exception('Không tạo được đơn hàng test.');
            }

            /*
            |--------------------------------------------------------------------------
            | 5. Trả IMEI cũ về available nếu trước đó đơn này từng gắn IMEI
            |--------------------------------------------------------------------------
            */
            $oldImeiIds = DB::table('order_items')
                ->where('order_id', $order->id)
                ->whereNotNull('imei_id')
                ->pluck('imei_id')
                ->all();

            if (! empty($oldImeiIds)) {
                DB::table('imeis')
                    ->whereIn('id', $oldImeiIds)
                    ->whereIn('status', ['reserved'])
                    ->update([
                        'status' => 'available',
                        'reserved_at' => null,
                        'reserved_by_order_item_id' => null,
                        'updated_at' => $now,
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 6. Xóa dữ liệu con cũ để seed lại không bị trùng
            |--------------------------------------------------------------------------
            */
            DB::table('shipment_items')
                ->whereIn('shipment_id', function ($query) use ($order) {
                    $query->select('id')
                        ->from('shipments')
                        ->where('order_id', $order->id);
                })
                ->delete();

            DB::table('order_proofs')
                ->where('order_id', $order->id)
                ->delete();

            DB::table('shipments')
                ->where('order_id', $order->id)
                ->delete();

            DB::table('payments')
                ->where('order_id', $order->id)
                ->delete();

            DB::table('order_items')
                ->where('order_id', $order->id)
                ->delete();

            DB::table('order_receivers')
                ->where('order_id', $order->id)
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | 7. Tạo chi tiết đơn hàng
            |--------------------------------------------------------------------------
            | Quan trọng:
            | - Không gắn imei_id.
            | - IMEI sẽ được chọn trong modal đóng gói.
            */
            DB::table('order_items')->insert([
                'order_id' => $order->id,
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->product_variant_id,
                'price' => $price,
                'quantity' => $quantity,
                'total' => $subtotal,
                'imei_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 8. Tạo thông tin người nhận
            |--------------------------------------------------------------------------
            | Phần này dùng để test phiếu giao hàng in thông tin người nhận,
            | không in nhầm thông tin khách hàng.
            */
            DB::table('order_receivers')->insert([
                'order_id' => $order->id,
                'receiver_name' => 'Người nhận test',
                'receiver_phone' => '0999999999',
                'receiver_address' => 'Tầng 5, Tòa nhà Test, 99 Nguyễn Văn Cừ, Hà Nội',
                'receiver_note' => 'Gọi trước khi giao hàng.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 9. Tạo thanh toán COD mẫu
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

            $this->command->info('Đã tạo ORD_TEST_SHIPMENT_001 ở trạng thái waiting_pack, chưa gắn IMEI.');
        });
    }
}
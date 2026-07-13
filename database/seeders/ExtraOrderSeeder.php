<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExtraOrderSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();

            // Lấy hoặc tạo customers
            $customer1 = DB::table('users')->where('email', 'customer.test@gmail.com')->first();
            $customer2 = DB::table('users')->where('role', 'customer')
                ->where('email', '!=', 'customer.test@gmail.com')
                ->first();

            // Nếu không có customer2 thì dùng customer1
            if (!$customer1) {
                throw new \Exception('Chưa có customer. Hãy chạy UserSeeder trước.');
            }
            if (!$customer2) {
                $customer2 = $customer1;
            }

            $c2Name  = $customer2->name  ?? $customer1->name;
            $c2Phone = $customer2->phone ?? $customer1->phone ?? '0911111111';

            // Lấy sản phẩm + variants
            $iphoneVariant = DB::table('product_variants')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->where('products.slug', 'iphone-16-pro')
                ->where('product_variants.color', 'Titan Trắng')
                ->where('products.storage', '256GB')
                ->select('product_variants.*', 'products.price as product_price', 'products.id as pid')
                ->first();

            $samsungVariant = DB::table('product_variants')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->where('products.slug', 'samsung-galaxy-s25')
                ->select('product_variants.*', 'products.price as product_price', 'products.id as pid')
                ->first();

            $xiaomiProduct = DB::table('products')->where('slug', 'xiaomi-redmi-note-15')->first();
            $oppoProduct   = DB::table('products')->where('slug', 'oppo-reno-12')->first();
            $airpodsProduct = DB::table('products')->where('slug', 'apple-airpods-pro-3')->first();

            // Lấy coupon
            $coupon = DB::table('coupons')->where('code', 'SALE20')->first();

            $orders = [
                // 1. Đơn processing - đang xử lý - đã thanh toán VNPay - có coupon
                [
                    'order_code'          => 'ORD_EXTRA_001',
                    'user_id'             => $customer1->id,
                    'customer_name'       => $customer1->name,
                    'customer_phone'      => $customer1->phone,
                    'shipping_address'    => 'Số 1 Cầu Giấy, Hà Nội',
                    'subtotal'            => $iphoneVariant ? (float)$iphoneVariant->product_price + (float)$iphoneVariant->additional_price : 32990000,
                    'membership_discount' => 0,
                    'coupon_discount'     => $coupon ? 5000000 : 0,
                    'coupon_id'           => $coupon?->id,
                    'coupon_code'         => $coupon?->code,
                    'total_amount'        => $iphoneVariant ? ((float)$iphoneVariant->product_price + (float)$iphoneVariant->additional_price - ($coupon ? 5000000 : 0)) : 27990000,
                    'status'              => 'processing',
                    'fulfillment_status'  => 'waiting_pack',
                    'confirmed_at'        => $now->copy()->subHours(2),
                    'packed_at'           => null,
                    'handed_over_at'      => null,
                    'delivered_at'        => null,
                    'cancelled_at'        => null,
                    'payment_method'      => 'vnpay',
                    'payment_status'      => 'paid',
                    'product_variant_id'  => $iphoneVariant?->id,
                    'product_id'          => $iphoneVariant?->pid,
                    'price'               => $iphoneVariant ? (float)$iphoneVariant->product_price + (float)$iphoneVariant->additional_price : 32990000,
                    'created_at'          => $now->copy()->subHours(3),
                ],
                // 2. Đơn shipping - đang giao hàng - thanh toán MoMo
                [
                    'order_code'          => 'ORD_EXTRA_002',
                    'user_id'             => $customer2->id,
                    'customer_name'       => $c2Name,
                    'customer_phone'      => $c2Phone,
                    'shipping_address'    => '123 Test Thực Tế, Hà Nội',
                    'subtotal'            => $samsungVariant ? (float)$samsungVariant->product_price + (float)$samsungVariant->additional_price : 24990000,
                    'membership_discount' => 0,
                    'coupon_discount'     => 0,
                    'coupon_id'           => null,
                    'coupon_code'         => null,
                    'total_amount'        => $samsungVariant ? (float)$samsungVariant->product_price + (float)$samsungVariant->additional_price : 24990000,
                    'status'              => 'shipping',
                    'fulfillment_status'  => 'shipping',
                    'confirmed_at'        => $now->copy()->subDays(2),
                    'packed_at'           => $now->copy()->subDays(1)->subHours(5),
                    'handed_over_at'      => $now->copy()->subDays(1),
                    'delivered_at'        => null,
                    'cancelled_at'        => null,
                    'payment_method'      => 'momo',
                    'payment_status'      => 'paid',
                    'product_variant_id'  => $samsungVariant?->id,
                    'product_id'          => $samsungVariant?->pid,
                    'price'               => $samsungVariant ? (float)$samsungVariant->product_price + (float)$samsungVariant->additional_price : 24990000,
                    'created_at'          => $now->copy()->subDays(2)->subHours(1),
                ],
                // 3. Đơn completed - hoàn thành - COD - membership discount
                [
                    'order_code'          => 'ORD_EXTRA_003',
                    'user_id'             => $customer1->id,
                    'customer_name'       => $customer1->name,
                    'customer_phone'      => $customer1->phone,
                    'shipping_address'    => 'Số 1 Cầu Giấy, Hà Nội',
                    'subtotal'            => $xiaomiProduct?->price ?? 6990000,
                    'membership_discount' => 200000,
                    'coupon_discount'     => 0,
                    'coupon_id'           => null,
                    'coupon_code'         => null,
                    'total_amount'        => ($xiaomiProduct?->price ?? 6990000) - 200000,
                    'status'              => 'completed',
                    'fulfillment_status'  => 'completed',
                    'confirmed_at'        => $now->copy()->subDays(5),
                    'packed_at'           => $now->copy()->subDays(5)->addHours(2),
                    'handed_over_at'      => $now->copy()->subDays(4),
                    'delivered_at'        => $now->copy()->subDays(3),
                    'cancelled_at'        => null,
                    'payment_method'      => 'cod',
                    'payment_status'      => 'paid',
                    'product_variant_id'  => null,
                    'product_id'          => $xiaomiProduct?->id,
                    'price'               => $xiaomiProduct?->price ?? 6990000,
                    'created_at'          => $now->copy()->subDays(6),
                ],
                // 4. Đơn cancelled - đã hủy - ZaloPay - có coupon
                [
                    'order_code'          => 'ORD_EXTRA_004',
                    'user_id'             => $customer2->id,
                    'customer_name'       => $c2Name,
                    'customer_phone'      => $c2Phone,
                    'shipping_address'    => '123 Test Thực Tế, Hà Nội',
                    'subtotal'            => $oppoProduct?->price ?? 8990000,
                    'membership_discount' => 0,
                    'coupon_discount'     => 899000,
                    'coupon_id'           => $coupon?->id,
                    'coupon_code'         => $coupon?->code,
                    'total_amount'        => ($oppoProduct?->price ?? 8990000) - 899000,
                    'status'              => 'cancelled',
                    'fulfillment_status'  => 'cancelled',
                    'confirmed_at'        => null,
                    'packed_at'           => null,
                    'handed_over_at'      => null,
                    'delivered_at'        => null,
                    'cancelled_at'        => $now->copy()->subDays(1),
                    'payment_method'      => 'zalopay',
                    'payment_status'      => 'refunded',
                    'product_variant_id'  => null,
                    'product_id'          => $oppoProduct?->id,
                    'price'               => $oppoProduct?->price ?? 8990000,
                    'created_at'          => $now->copy()->subDays(1)->subHours(3),
                ],
                // 5. Đơn pending - chờ xác nhận - COD - nhiều sản phẩm
                [
                    'order_code'          => 'ORD_EXTRA_005',
                    'user_id'             => $customer1->id,
                    'customer_name'       => $customer1->name,
                    'customer_phone'      => $customer1->phone,
                    'shipping_address'    => 'Số 1 Cầu Giấy, Hà Nội',
                    'subtotal'            => ($airpodsProduct?->price ?? 5990000) * 2,
                    'membership_discount' => 0,
                    'coupon_discount'     => 0,
                    'coupon_id'           => null,
                    'coupon_code'         => null,
                    'total_amount'        => ($airpodsProduct?->price ?? 5990000) * 2,
                    'status'              => 'pending',
                    'fulfillment_status'  => 'pending',
                    'confirmed_at'        => null,
                    'packed_at'           => null,
                    'handed_over_at'      => null,
                    'delivered_at'        => null,
                    'cancelled_at'        => null,
                    'payment_method'      => 'cod',
                    'payment_status'      => 'pending',
                    'product_variant_id'  => null,
                    'product_id'          => $airpodsProduct?->id,
                    'price'               => $airpodsProduct?->price ?? 5990000,
                    'created_at'          => $now->copy()->subMinutes(30),
                ],
            ];

            foreach ($orders as $orderData) {
                // Xóa đơn cũ nếu tồn tại
                $old = DB::table('orders')->where('order_code', $orderData['order_code'])->first();
                if ($old) {
                    DB::table('order_items')->where('order_id', $old->id)->delete();
                    DB::table('payments')->where('order_id', $old->id)->delete();
                    DB::table('orders')->where('id', $old->id)->delete();
                }

                // Tạo đơn hàng
                $orderId = DB::table('orders')->insertGetId([
                    'user_id'             => $orderData['user_id'],
                    'order_code'          => $orderData['order_code'],
                    'customer_name'       => $orderData['customer_name'],
                    'customer_phone'      => $orderData['customer_phone'],
                    'shipping_address'    => $orderData['shipping_address'],
                    'subtotal'            => $orderData['subtotal'],
                    'membership_discount' => $orderData['membership_discount'],
                    'coupon_discount'     => $orderData['coupon_discount'],
                    'coupon_id'           => $orderData['coupon_id'],
                    'coupon_code'         => $orderData['coupon_code'],
                    'total_amount'        => $orderData['total_amount'],
                    'status'              => $orderData['status'],
                    'fulfillment_status'  => $orderData['fulfillment_status'],
                    'confirmed_at'        => $orderData['confirmed_at'],
                    'packed_at'           => $orderData['packed_at'],
                    'handed_over_at'      => $orderData['handed_over_at'],
                    'delivered_at'        => $orderData['delivered_at'],
                    'cancelled_at'        => $orderData['cancelled_at'],
                    'shipping_label_printed_at' => null,
                    'created_at'          => $orderData['created_at'],
                    'updated_at'          => $now,
                ]);

                // Tạo order item (2 items nếu là đơn 005)
                $qty = $orderData['order_code'] === 'ORD_EXTRA_005' ? 2 : 1;
                DB::table('order_items')->insert([
                    'order_id'           => $orderId,
                    'product_id'         => $orderData['product_id'],
                    'product_variant_id' => $orderData['product_variant_id'],
                    'price'              => $orderData['price'],
                    'quantity'           => $qty,
                    'total'              => $orderData['price'] * $qty,
                    'imei_id'            => null,
                    'created_at'         => $orderData['created_at'],
                    'updated_at'         => $now,
                ]);

                // Tạo payment
                DB::table('payments')->insert([
                    'order_id'         => $orderId,
                    'payment_method'   => $orderData['payment_method'],
                    'amount'           => $orderData['total_amount'],
                    'payment_status'   => $orderData['payment_status'],
                    'transaction_code' => in_array($orderData['payment_status'], ['paid', 'refunded'])
                        ? 'TXN_' . $orderData['order_code']
                        : null,
                    'paid_at'          => $orderData['payment_status'] === 'paid'
                        ? $orderData['confirmed_at']
                        : null,
                    'created_at'       => $orderData['created_at'],
                    'updated_at'       => $now,
                ]);

                $this->command->info('✓ ' . $orderData['order_code'] . ' — ' . $orderData['status'] . ' | ' . $orderData['payment_method']);
            }
        });
    }
}

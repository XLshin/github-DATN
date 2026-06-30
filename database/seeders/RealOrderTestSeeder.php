<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RealOrderTestSeeder extends Seeder
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
            DB::table('users')->updateOrInsert(
                ['email' => 'real.customer@gmail.com'],
                [
                    'name' => 'Khách Test Thực Tế',
                    'phone' => '0911111111',
                    'address' => '123 Test Thực Tế, Hà Nội',
                    'email_verified_at' => $now,
                    'password' => Hash::make('12345678'),
                    'points' => 0,
                    'role' => 'customer',
                    'total_spent' => 0,
                    'membership_level' => 'bronze',
                    'remember_token' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $customer = DB::table('users')
                ->where('email', 'real.customer@gmail.com')
                ->first();

            if (! $customer) {
                throw new \Exception('Không tạo được customer test.');
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Xóa các đơn test cũ ORD_REAL_TEST_%
            | Đồng thời trả IMEI cũ về available
            |--------------------------------------------------------------------------
            */
            $oldOrders = DB::table('orders')
                ->where('order_code', 'like', 'ORD_REAL_TEST_%')
                ->get();

            foreach ($oldOrders as $oldOrder) {
                $oldImeiIds = DB::table('order_items')
                    ->where('order_id', $oldOrder->id)
                    ->whereNotNull('imei_id')
                    ->pluck('imei_id')
                    ->all();

                if (! empty($oldImeiIds)) {
                    DB::table('imeis')
                        ->whereIn('id', $oldImeiIds)
                        ->update([
                            'status' => 'available',
                            'reserved_at' => null,
                            'reserved_by_order_item_id' => null,
                            'updated_at' => $now,
                        ]);
                }

                DB::table('order_proofs')
                    ->where('order_id', $oldOrder->id)
                    ->delete();

                DB::table('shipments')
                    ->where('order_id', $oldOrder->id)
                    ->delete();

                DB::table('payments')
                    ->where('order_id', $oldOrder->id)
                    ->delete();

                DB::table('order_items')
                    ->where('order_id', $oldOrder->id)
                    ->delete();

                DB::table('orders')
                    ->where('id', $oldOrder->id)
                    ->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Đảm bảo iPhone 16 Pro là sản phẩm quản lý theo IMEI
            |--------------------------------------------------------------------------
            */
            DB::table('products')
                ->where('slug', 'iphone-16-pro')
                ->update([
                    'product_type' => 'imei/serial',
                    'updated_at' => $now,
                ]);

            /*
            |--------------------------------------------------------------------------
            | 4. Danh sách 5 biến thể cần tạo đơn test
            |--------------------------------------------------------------------------
            */
            $testCases = [
                [
                    'order_code' => 'ORD_REAL_TEST_001',
                    'customer_name' => 'Khách Test Titan Đen 128GB',
                    'customer_phone' => '0911111101',
                    'shipping_address' => '101 Test Variant, Hà Nội',
                    'product_slug' => 'iphone-16-pro',
                    'color' => 'Titan Đen',
                    'storage' => '128GB',
                    'payment_method' => 'cod',
                    'payment_status' => 'pending',
                ],
                [
                    'order_code' => 'ORD_REAL_TEST_002',
                    'customer_name' => 'Khách Test Titan Đen 256GB',
                    'customer_phone' => '0911111102',
                    'shipping_address' => '102 Test Variant, Hà Nội',
                    'product_slug' => 'iphone-16-pro',
                    'color' => 'Titan Đen',
                    'storage' => '256GB',
                    'payment_method' => 'cod',
                    'payment_status' => 'pending',
                ],
                [
                    'order_code' => 'ORD_REAL_TEST_003',
                    'customer_name' => 'Khách Test Titan Trắng 128GB',
                    'customer_phone' => '0911111103',
                    'shipping_address' => '103 Test Variant, Hà Nội',
                    'product_slug' => 'iphone-16-pro',
                    'color' => 'Titan Trắng',
                    'storage' => '128GB',
                    'payment_method' => 'cod',
                    'payment_status' => 'pending',
                ],
                [
                    'order_code' => 'ORD_REAL_TEST_004',
                    'customer_name' => 'Khách Test Titan Trắng 256GB',
                    'customer_phone' => '0911111104',
                    'shipping_address' => '104 Test Variant, Hà Nội',
                    'product_slug' => 'iphone-16-pro',
                    'color' => 'Titan Trắng',
                    'storage' => '256GB',
                    'payment_method' => 'vnpay',
                    'payment_status' => 'paid',
                ],
                [
                    'order_code' => 'ORD_REAL_TEST_005',
                    'customer_name' => 'Khách Test Titan Sa Mạc 128GB',
                    'customer_phone' => '0911111105',
                    'shipping_address' => '105 Test Variant, Hà Nội',
                    'product_slug' => 'iphone-16-pro',
                    'color' => 'Titan Sa Mạc',
                    'storage' => '128GB',
                    'payment_method' => 'momo',
                    'payment_status' => 'paid',
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | 5. Tạo từng đơn test từ biến thể + IMEI available có sẵn
            |--------------------------------------------------------------------------
            */
            foreach ($testCases as $case) {
                $this->createRealOrderFromVariant($customer->id, $case, $now);
            }

            $this->command->info('Đã tạo 5 đơn test thực tế thành công.');
            $this->command->info('Các mã đơn: ORD_REAL_TEST_001 đến ORD_REAL_TEST_005');
            $this->command->info('Customer: real.customer@gmail.com / 12345678');
        });
    }

    private function createRealOrderFromVariant(int $customerId, array $case, $now): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Lấy đúng biến thể + IMEI available
        |--------------------------------------------------------------------------
        */
        $imeiData = DB::table('imeis')
            ->join('product_variants', 'product_variants.id', '=', 'imeis.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('imeis.status', 'available')
            ->where('products.status', true)
            ->where('product_variants.status', true)
            ->where('products.slug', $case['product_slug'])
            ->where('product_variants.color', $case['color'])
            ->where('product_variants.storage', $case['storage'])
            ->select(
                'imeis.id as imei_id',
                'imeis.imei',
                'product_variants.id as variant_id',
                'product_variants.color',
                'product_variants.storage',
                'product_variants.additional_price',
                'products.id as product_id',
                'products.name as product_name',
                'products.price as product_price',
                'products.product_type'
            )
            ->orderBy('imeis.id')
            ->first();

        if (! $imeiData) {
            throw new \Exception(
                'Không tìm thấy IMEI available cho '
                . $case['product_slug'] . ' - '
                . $case['color'] . ' - '
                . $case['storage']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Tính giá
        |--------------------------------------------------------------------------
        */
        $price = (float) $imeiData->product_price + (float) ($imeiData->additional_price ?? 0);
        $quantity = 1;
        $subtotal = $price * $quantity;

        /*
        |--------------------------------------------------------------------------
        | 3. Tạo đơn hàng giống khách vừa đặt
        |--------------------------------------------------------------------------
        */
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $customerId,
            'order_code' => $case['order_code'],
            'customer_name' => $case['customer_name'],
            'customer_phone' => $case['customer_phone'],
            'shipping_address' => $case['shipping_address'],
            'subtotal' => $subtotal,
            'membership_discount' => 0,
            'coupon_discount' => 0,
            'total_amount' => $subtotal,
            'status' => 'pending',
            'fulfillment_status' => 'pending',
            'confirmed_at' => null,
            'packed_at' => null,
            'handed_over_at' => null,
            'delivered_at' => null,
            'cancelled_at' => null,
            'shipping_label_printed_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 4. Tạo order_item và gắn IMEI thật
        |--------------------------------------------------------------------------
        */
        $orderItemId = DB::table('order_items')->insertGetId([
            'order_id' => $orderId,
            'product_id' => $imeiData->product_id,
            'product_variant_id' => $imeiData->variant_id,
            'price' => $price,
            'quantity' => $quantity,
            'total' => $subtotal,
            'imei_id' => $imeiData->imei_id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 5. Chuyển IMEI available -> reserved
        |--------------------------------------------------------------------------
        */
        DB::table('imeis')
            ->where('id', $imeiData->imei_id)
            ->update([
                'status' => 'reserved',
                'reserved_at' => $now,
                'reserved_by_order_item_id' => $orderItemId,
                'updated_at' => $now,
            ]);

        /*
        |--------------------------------------------------------------------------
        | 6. Tạo payment
        |--------------------------------------------------------------------------
        */
        DB::table('payments')->insert([
            'order_id' => $orderId,
            'payment_method' => $case['payment_method'],
            'amount' => $subtotal,
            'payment_status' => $case['payment_status'],
            'transaction_code' => $case['payment_status'] === 'paid'
                ? 'TXN_' . $case['order_code']
                : null,
            'paid_at' => $case['payment_status'] === 'paid' ? $now : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->command->info(
            'Đã tạo ' . $case['order_code']
            . ' | ' . $imeiData->product_name
            . ' | ' . $imeiData->color
            . ' - ' . $imeiData->storage
            . ' | IMEI: ' . $imeiData->imei
        );
    }
}
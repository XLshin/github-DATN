<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarrantyTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();

            /*
            |--------------------------------------------------------------------------
            | 1. Tạo (hoặc lấy lại) đơn hàng RIÊNG cho warranty test
            |--------------------------------------------------------------------------
            | Trước đây seeder này gắn ké 4 order_items vào đơn ORD_TEST_SHIPMENT_001
            | (vốn dành cho test đóng gói/giao hàng, chỉ nên có đúng 1 dòng chưa gán IMEI),
            | khiến đơn đó bị phình ra nhiều dòng sản phẩm không liên quan.
            | Nay tạo hẳn 1 đơn độc lập: ORD_TEST_WARRANTY_001.
            |--------------------------------------------------------------------------
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

            if (!$variant) {
                throw new \Exception('Cần có product_variants trước khi seed bảo hành. Hãy chạy ProductSeeder và ProductVariantSeeder trước.');
            }

            $customer = DB::table('users')
                ->where('email', 'customer.test@gmail.com')
                ->first();

            if (!$customer) {
                $customer = DB::table('users')->where('role', 'customer')->first();
            }

            if (!$customer) {
                throw new \Exception('Cần có ít nhất 1 user customer trước khi seed bảo hành. Hãy chạy UserSeeder/OrderSeeder trước.');
            }

            $orderData = [
                'user_id' => $customer->id,
                'customer_name' => 'Khách hàng test bảo hành',
                'customer_phone' => '0987650000',
                'shipping_address' => 'Số 1 Cầu Giấy, Hà Nội',
                'subtotal' => 0,
                'membership_discount' => 0,
                'coupon_discount' => 0,
                'total_amount' => 0,
                'status' => 'completed',
                'fulfillment_status' => 'completed',
                'confirmed_at' => $now,
                'packed_at' => $now,
                'handed_over_at' => $now,
                'delivered_at' => $now,
                'cancelled_at' => null,
                'shipping_label_printed_at' => null,
                'updated_at' => $now,
            ];

            if (DB::table('orders')->where('order_code', 'ORD_TEST_WARRANTY_001')->exists()) {
                DB::table('orders')
                    ->where('order_code', 'ORD_TEST_WARRANTY_001')
                    ->update($orderData);
            } else {
                $orderData['order_code'] = 'ORD_TEST_WARRANTY_001';
                $orderData['created_at'] = $now;

                DB::table('orders')->insert($orderData);
            }

            $order = DB::table('orders')
                ->where('order_code', 'ORD_TEST_WARRANTY_001')
                ->first();

            if (!$order) {
                throw new \Exception('Không tạo được đơn hàng riêng cho warranty test.');
            }

            $price = (float) $variant->product_price + (float) ($variant->additional_price ?? 0);

            // 4 IMEI test (case 1-4) đều quantity = 1 => tổng tiền đơn = 4 x price.
            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'subtotal' => $price * 4,
                    'total_amount' => $price * 4,
                    'updated_at' => $now,
                ]);

            /*
            |--------------------------------------------------------------------------
            | 4. Xóa dữ liệu test cũ để seed lại không bị rác
            |--------------------------------------------------------------------------
            */
            $testImeis = [
                'IMEI-TEST-000001',
                'IMEI-TEST-000002',
                'IMEI-TEST-000003',
                'IMEI-TEST-000004',
            ];

            $oldImeiIds = DB::table('imeis')
                ->whereIn('imei', $testImeis)
                ->pluck('id')
                ->all();

            if (!empty($oldImeiIds)) {
                DB::table('warranties')
                    ->whereIn('imei_id', $oldImeiIds)
                    ->delete();

                DB::table('order_items')
                    ->whereIn('imei_id', $oldImeiIds)
                    ->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | 5. Case 1: IMEI đã bán + đang có phiếu active
            | Dùng để test: không cho tạo thêm phiếu mới vì còn phiếu mở.
            |--------------------------------------------------------------------------
            */
            $imeiActive = $this->createSoldImeiWithOrderItem(
                imeiCode: 'IMEI-TEST-000001',
                imeiStatus: 'sold',
                orderId: $order->id,
                productId: $variant->product_id,
                productVariantId: $variant->product_variant_id,
                price: $price,
                now: $now
            );

            DB::table('warranties')->insert([
                'imei_id' => $imeiActive->id,
                'order_id' => $order->id,
                'warranty_start' => $now->copy()->subMonth()->toDateString(),
                'warranty_end' => $now->copy()->addMonths(11)->toDateString(),
                'status' => 'active',
                'created_at' => $now->copy()->subMonth(),
                'updated_at' => $now,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 6. Case 2: IMEI đã bán + chỉ có phiếu expired
            | Dùng để test: cho phép tạo phiếu bảo hành mới.
            |--------------------------------------------------------------------------
            */
            $imeiExpired = $this->createSoldImeiWithOrderItem(
                imeiCode: 'IMEI-TEST-000002',
                imeiStatus: 'sold',
                orderId: $order->id,
                productId: $variant->product_id,
                productVariantId: $variant->product_variant_id,
                price: $price,
                now: $now
            );

            DB::table('warranties')->insert([
                'imei_id' => $imeiExpired->id,
                'order_id' => $order->id,
                'warranty_start' => $now->copy()->subYears(2)->toDateString(),
                'warranty_end' => $now->copy()->subYear()->toDateString(),
                'status' => 'expired',
                'created_at' => $now->copy()->subYears(2),
                'updated_at' => $now,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 7. Case 3: IMEI đã bán + chưa có phiếu bảo hành
            | Dùng để test: tạo phiếu mới bình thường.
            |--------------------------------------------------------------------------
            */
            $this->createSoldImeiWithOrderItem(
                imeiCode: 'IMEI-TEST-000003',
                imeiStatus: 'sold',
                orderId: $order->id,
                productId: $variant->product_id,
                productVariantId: $variant->product_variant_id,
                price: $price,
                now: $now
            );

            /*
            |--------------------------------------------------------------------------
            | 8. Case 4: IMEI đang bảo hành + phiếu claimed
            | Dùng để test: tra cứu IMEI đang trong quá trình bảo hành.
            |--------------------------------------------------------------------------
            */
            $imeiClaimed = $this->createSoldImeiWithOrderItem(
                imeiCode: 'IMEI-TEST-000004',
                imeiStatus: 'warranty',
                orderId: $order->id,
                productId: $variant->product_id,
                productVariantId: $variant->product_variant_id,
                price: $price,
                now: $now
            );

            DB::table('warranties')->insert([
                'imei_id' => $imeiClaimed->id,
                'order_id' => $order->id,
                'warranty_start' => $now->copy()->subMonth()->toDateString(),
                'warranty_end' => $now->copy()->addMonths(11)->toDateString(),
                'status' => 'claimed',
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now,
            ]);

            $this->command->info('Seed dữ liệu bảo hành thành công.');
            $this->command->info('Case 1 - Có phiếu active, không tạo thêm: IMEI-TEST-000001');
            $this->command->info('Case 2 - Phiếu cũ expired, có thể tạo phiếu mới: IMEI-TEST-000002');
            $this->command->info('Case 3 - Chưa có phiếu, có thể tạo phiếu mới: IMEI-TEST-000003');
            $this->command->info('Case 4 - Đang bảo hành claimed: IMEI-TEST-000004');
            $this->command->info('Mã đơn test (riêng, không còn gắn vào ORD_TEST_SHIPMENT_001): ' . $order->order_code);
        });
    }

    private function createSoldImeiWithOrderItem(
        string $imeiCode,
        string $imeiStatus,
        int $orderId,
        int $productId,
        int $productVariantId,
        float $price,
        $now
    ) {
        DB::table('imeis')->updateOrInsert(
            ['imei' => $imeiCode],
            [
                'product_variant_id' => $productVariantId,
                'status' => $imeiStatus,
                'reserved_at' => null,
                'reserved_by_order_item_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $imei = DB::table('imeis')
            ->where('imei', $imeiCode)
            ->first();

        $orderItemId = DB::table('order_items')->insertGetId([
            'order_id' => $orderId,
            'product_id' => $productId,
            'product_variant_id' => $productVariantId,
            'price' => $price,
            'quantity' => 1,
            'total' => $price,
            'imei_id' => $imei->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('imeis')
            ->where('id', $imei->id)
            ->update([
                'status' => $imeiStatus,
                'reserved_by_order_item_id' => $orderItemId,
                'updated_at' => $now,
            ]);

        return DB::table('imeis')
            ->where('id', $imei->id)
            ->first();
    }
}
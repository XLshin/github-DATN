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
            | 1. Lấy đơn hàng test
            |--------------------------------------------------------------------------
            */
            $order = DB::table('orders')
                ->where('order_code', 'ORD_TEST_SHIPMENT_001')
                ->first();

            if (!$order) {
                $order = DB::table('orders')->first();
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Lấy biến thể sản phẩm hợp lệ
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

            if (!$order || !$variant) {
                throw new \Exception('Cần có orders và product_variants trước khi seed bảo hành. Hãy chạy ProductSeeder, ProductVariantSeeder và OrderSeeder trước.');
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Cập nhật đơn hàng thành completed để giống đơn đã bán
            |--------------------------------------------------------------------------
            */
            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'status' => 'completed',
                    'fulfillment_status' => 'completed',
                    'delivered_at' => $now,
                    'updated_at' => $now,
                ]);

            $price = (float) $variant->product_price + (float) ($variant->additional_price ?? 0);

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
            $this->command->info('Mã đơn test: ' . $order->order_code);
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
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
            | 1. Lấy đơn hàng và biến thể sản phẩm có sẵn
            |--------------------------------------------------------------------------
            */
            $order = DB::table('orders')
                ->where('order_code', 'ORD_TEST_SHIPMENT_001')
                ->first();

            if (!$order) {
                $order = DB::table('orders')->first();
            }

            $variant = DB::table('product_variants')->first();

            if (!$order || !$variant) {
                throw new \Exception('Cần có orders và product_variants trước khi seed bảo hành. Hãy chạy ProductSeeder, ProductVariantSeeder và OrderSeeder trước.');
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Cập nhật đơn hàng thành completed để test bảo hành
            |--------------------------------------------------------------------------
            */
            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'status' => 'completed',
                    'updated_at' => $now,
                ]);

            /*
            |--------------------------------------------------------------------------
            | 3. Đảm bảo đơn hàng có item chứa variant này
            |--------------------------------------------------------------------------
            */
            DB::table('order_items')->updateOrInsert(
                [
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                ],
                [
                    'product_id' => $variant->product_id,
                    'price' => 10000000,
                    'quantity' => 1,
                    'total' => 10000000,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 4. Case 1: IMEI đang có phiếu bảo hành active
            |--------------------------------------------------------------------------
            */
            DB::table('imeis')->updateOrInsert(
                ['imei' => 'IMEI-TEST-000001'],
                [
                    'product_variant_id' => $variant->id,
                    'status' => 'sold',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $imeiActive = DB::table('imeis')
                ->where('imei', 'IMEI-TEST-000001')
                ->first();

            DB::table('warranties')->updateOrInsert(
                [
                    'imei_id' => $imeiActive->id,
                    'order_id' => $order->id,
                ],
                [
                    'warranty_start' => $now->copy()->subMonth()->toDateString(),
                    'warranty_end' => $now->copy()->addMonths(11)->toDateString(),
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 5. Case 2: IMEI chỉ có phiếu bảo hành expired
            |--------------------------------------------------------------------------
            */
            DB::table('imeis')->updateOrInsert(
                ['imei' => 'IMEI-TEST-000002'],
                [
                    'product_variant_id' => $variant->id,
                    'status' => 'sold',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $imeiExpired = DB::table('imeis')
                ->where('imei', 'IMEI-TEST-000002')
                ->first();

            DB::table('warranties')->updateOrInsert(
                [
                    'imei_id' => $imeiExpired->id,
                    'order_id' => $order->id,
                ],
                [
                    'warranty_start' => $now->copy()->subYears(2)->toDateString(),
                    'warranty_end' => $now->copy()->subYear()->toDateString(),
                    'status' => 'expired',
                    'created_at' => $now->copy()->subYears(2),
                    'updated_at' => $now,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 6. Case 3: IMEI chưa có phiếu bảo hành
            |--------------------------------------------------------------------------
            */
            DB::table('imeis')->updateOrInsert(
                ['imei' => 'IMEI-TEST-000003'],
                [
                    'product_variant_id' => $variant->id,
                    'status' => 'sold',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $this->command->info('Seed dữ liệu bảo hành thành công.');
            $this->command->info('IMEI active: IMEI-TEST-000001');
            $this->command->info('IMEI expired: IMEI-TEST-000002');
            $this->command->info('IMEI chưa có bảo hành: IMEI-TEST-000003');
            $this->command->info('Mã đơn test: ' . $order->order_code);
        });
    }
}

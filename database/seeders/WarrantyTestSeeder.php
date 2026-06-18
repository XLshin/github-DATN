<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarrantyTestSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $order = DB::table('orders')->first();
        $variant = DB::table('product_variants')->first();

        if (! $order || ! $variant) {
            $this->command->warn('Cần có orders và product_variants trước khi seed bảo hành.');
            return;
        }

        DB::table('orders')
            ->where('id', $order->id)
            ->update([
                'status' => 'completed',
                'updated_at' => $now,
            ]);

        $hasOrderItem = DB::table('order_items')
            ->where('order_id', $order->id)
            ->where('product_variant_id', $variant->id)
            ->exists();

        if (! $hasOrderItem) {
            DB::table('order_items')->insert([
                'order_id' => $order->id,
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'price' => 10000000,
                'quantity' => 1,
                'total' => 10000000,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Case 1: IMEI đang có phiếu active
        $imeiActiveId = DB::table('imeis')->insertGetId([
            'product_variant_id' => $variant->id,
            'imei' => 'IMEI-TEST-000001',
            'status' => 'sold',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('warranties')->insert([
            'imei_id' => $imeiActiveId,
            'order_id' => $order->id,
            'warranty_start' => $now->copy()->subMonth()->toDateString(),
            'warranty_end' => $now->copy()->addMonths(11)->toDateString(),
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Case 2: IMEI chỉ có phiếu expired
        $imeiExpiredId = DB::table('imeis')->insertGetId([
            'product_variant_id' => $variant->id,
            'imei' => 'IMEI-TEST-000002',
            'status' => 'sold',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('warranties')->insert([
            'imei_id' => $imeiExpiredId,
            'order_id' => $order->id,
            'warranty_start' => $now->copy()->subYears(2)->toDateString(),
            'warranty_end' => $now->copy()->subYear()->toDateString(),
            'status' => 'expired',
            'created_at' => $now->copy()->subYears(2),
            'updated_at' => $now->copy()->subYear(),
        ]);

        // Case 3: IMEI chưa có phiếu bảo hành
        DB::table('imeis')->insert([
            'product_variant_id' => $variant->id,
            'imei' => 'IMEI-TEST-000003',
            'status' => 'sold',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->command->info('Seed dữ liệu bảo hành thành công.');
        $this->command->info('IMEI active: IMEI-TEST-000001');
        $this->command->info('IMEI expired: IMEI-TEST-000002');
        $this->command->info('IMEI chưa có bảo hành: IMEI-TEST-000003');
        $this->command->info('Mã đơn test: ' . $order->order_code);
    }
}
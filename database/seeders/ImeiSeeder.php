<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImeiSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $variants = DB::table('product_variants')->get();

        if ($variants->isEmpty()) {
            throw new \Exception('Chưa có product_variants. Hãy chạy ProductVariantSeeder trước.');
        }

        $counter = 1;
        foreach ($variants as $variant) {
            // Mỗi variant tạo 5 IMEI available
            for ($i = 1; $i <= 5; $i++) {
                $imei = 'IMEI' . str_pad($counter, 12, '0', STR_PAD_LEFT);
                DB::table('imeis')->updateOrInsert(
                    ['imei' => $imei],
                    [
                        'product_variant_id' => $variant->id,
                        'status' => 'available',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
                $counter++;
            }
        }

        $this->command->info("Đã tạo " . ($counter - 1) . " IMEI mẫu.");
    }
}

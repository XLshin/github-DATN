<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Reset giỏ hàng về dữ liệu mẫu test đa dạng.
 *
 * Chạy độc lập: php artisan db:seed --class=CartResetSeeder
 *
 * ┌──────────────────────────────────────────────────────────────────────────
 * │ TK 1  customer.test@gmail.com   (Bronze, 0 pt)
 * │       • Xiaomi Redmi Note 15 – Đen 128GB        × 1   (quantity)
 * │       • Apple AirPods Pro 3 – Trắng 1 unit      × 2   (quantity)
 * │       Subtotal ≈ 6,990,000 + 11,980,000 = 18,970,000₫
 * │       Coupon test: GIAM500K (500k off, min 5tr)
 * │
 * │ TK 2  bronze.test@gmail.com     (Bronze, 0 pt)
 * │       • iPhone 16 Pro – Titan Đen 128GB          × 1   (imei/serial)
 * │       • Xiaomi Redmi Note 15 – Xanh 256GB        × 2   (quantity)
 * │       Subtotal ≈ 29,990,000 + 15,980,000 = 45,970,000₫
 * │       Coupon test: SALE20 (20% off, min 10tr)
 * │
 * │ TK 3  silver.test@gmail.com     (Silver 2%, 500 pt)
 * │       • Samsung Galaxy S25 – Đen 256GB           × 1   (imei/serial)
 * │       • Oppo Reno 12 – Xanh 128GB               × 1   (quantity)
 * │       • Samsung Galaxy Buds 3 – Đen 1 unit       × 1   (quantity)
 * │       Subtotal ≈ 24,990,000 + 8,990,000 + 2,490,000 = 36,470,000₫
 * │       CK hội viên 2% trước, sau đó WELCOME10 (10%)
 * │
 * │ TK 4  gold.test@gmail.com       (Gold 5%, 2000 pt)
 * │       • iPhone 16 Pro – Titan Trắng 256GB        × 1   (imei/serial)
 * │       • iPad Air 6 – Bạc 256GB                  × 1   (quantity)
 * │       Subtotal ≈ 29,990,000 + 22,990,000 = 52,980,000₫
 * │       CK hội viên 5% + VIP2000K + 200 điểm = nhiều lớp chiết khấu
 * └──────────────────────────────────────────────────────────────────────────
 *
 * Lưu ý:
 *  - Giá hiển thị = product.price (additional_price chưa cộng vào cart, chỉ checkout mới tính)
 *  - Sản phẩm imei/serial: IMEI sẽ được reserve tự động khi checkout
 *  - Mật khẩu tất cả: Password@123
 */
class CartResetSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ──────────────────────────────────────────────
        // Map email → kịch bản giỏ hàng
        // Format: [product_id, variant_id, quantity]
        // ──────────────────────────────────────────────
        $scenarios = [
            'customer.test@gmail.com' => [
                // Xiaomi Redmi Note 15 – Đen 128GB (variant 7, quantity)
                [3, 7, 1],
                // Apple AirPods Pro 3 – Trắng 1 unit (variant 15, quantity)
                [7, 15, 2],
            ],
            'bronze.test@gmail.com' => [
                // iPhone 16 Pro – Titan Đen 128GB (variant 1, imei/serial)
                [1, 1, 1],
                // Xiaomi Redmi Note 15 – Xanh 256GB (variant 8, quantity)
                [3, 8, 2],
            ],
            'silver.test@gmail.com' => [
                // Samsung Galaxy S25 – Đen 256GB (variant 5, imei/serial)
                [2, 5, 1],
                // Oppo Reno 12 – Xanh 128GB (variant 9, quantity)
                [4, 9, 1],
                // Samsung Galaxy Buds 3 – Đen 1 unit (variant 16, quantity)
                [8, 16, 1],
            ],
            'gold.test@gmail.com' => [
                // iPhone 16 Pro – Titan Trắng 256GB (variant 4, imei/serial)
                [1, 4, 1],
                // iPad Air 6 – Bạc 256GB (variant 12, quantity)
                [5, 12, 1],
            ],
        ];

        foreach ($scenarios as $email => $cartRows) {
            $userId = DB::table('users')->where('email', $email)->value('id');

            if (! $userId) {
                $this->command->warn("Không tìm thấy user: {$email} – bỏ qua.");
                continue;
            }

            // Lấy hoặc tạo cart
            $cart = DB::table('carts')->where('user_id', $userId)->first();
            if ($cart) {
                DB::table('cart_items')->where('cart_id', $cart->id)->delete();
                $cartId = $cart->id;
            } else {
                $cartId = DB::table('carts')->insertGetId([
                    'user_id'    => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Thêm items
            foreach ($cartRows as [$productId, $variantId, $qty]) {
                DB::table('cart_items')->insert([
                    'cart_id'            => $cartId,
                    'product_id'         => $productId,
                    'product_variant_id' => $variantId,
                    'quantity'           => $qty,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
            }

            $this->command->info("✓ Đã reset giỏ hàng cho {$email} ({$qty} dòng)");
        }

        // Đảm bảo đủ IMEI available cho S25 (variant 5) và iPhone Trắng 256 (variant 4)
        $this->ensureImeis([4, 5], $now);

        // In bảng hướng dẫn test
        $this->printGuide();
    }

    private function ensureImeis(array $variantIds, $now): void
    {
        foreach ($variantIds as $variantId) {
            $count = DB::table('imeis')
                ->where('product_variant_id', $variantId)
                ->where('status', 'available')
                ->count();

            if ($count < 3) {
                $toAdd = 3 - $count;
                $existing = DB::table('imeis')->pluck('imei')->toArray();
                $added = 0;
                $suffix = 700000 + ($variantId * 100);

                while ($added < $toAdd) {
                    $imei = 'TST' . str_pad($suffix++, 12, '0', STR_PAD_LEFT);
                    if (! in_array($imei, $existing)) {
                        DB::table('imeis')->insert([
                            'product_variant_id' => $variantId,
                            'imei'               => $imei,
                            'status'             => 'available',
                            'created_at'         => $now,
                            'updated_at'         => $now,
                        ]);
                        $existing[] = $imei;
                        $added++;
                    }
                }

                $this->command->info("✓ Đã thêm {$added} IMEI cho variant #{$variantId}");
            }
        }
    }

    private function printGuide(): void
    {
        $this->command->newLine();
        $this->command->line('╔═══════════════════════════════════════════════════════════════════╗');
        $this->command->line('║         DỮ LIỆU TEST GIỎ HÀNG & THANH TOÁN (CartResetSeeder)    ║');
        $this->command->line('╠═══════════════════════════════════════════════════════════════════╣');
        $this->command->line('║  Tất cả mật khẩu: Password@123                                   ║');
        $this->command->line('╠════════════╦══════════╦════════╦══════════════════════════════════╣');
        $this->command->line('║ Email      ║ Hạng     ║ Điểm   ║ Nội dung giỏ hàng                ║');
        $this->command->line('╠════════════╬══════════╬════════╬══════════════════════════════════╣');
        $this->command->line('║ customer   ║ Bronze   ║  0     ║ Xiaomi Đen 128GB ×1              ║');
        $this->command->line('║ .test      ║          ║        ║ AirPods Trắng ×2                 ║');
        $this->command->line('║            ║          ║        ║ Subtotal ≈ 18,970,000₫           ║');
        $this->command->line('╠════════════╬══════════╬════════╬══════════════════════════════════╣');
        $this->command->line('║ bronze     ║ Bronze   ║  0     ║ iPhone Titan Đen 128GB ×1 [IMEI] ║');
        $this->command->line('║ .test      ║          ║        ║ Xiaomi Xanh 256GB ×2             ║');
        $this->command->line('║            ║          ║        ║ Subtotal ≈ 45,970,000₫           ║');
        $this->command->line('╠════════════╬══════════╬════════╬══════════════════════════════════╣');
        $this->command->line('║ silver     ║ Silver   ║ 500    ║ Samsung S25 Đen 256GB ×1 [IMEI]  ║');
        $this->command->line('║ .test      ║ (−2%)    ║        ║ Oppo Reno Xanh 128GB ×1          ║');
        $this->command->line('║            ║          ║        ║ Galaxy Buds Đen ×1               ║');
        $this->command->line('║            ║          ║        ║ Subtotal ≈ 36,470,000₫           ║');
        $this->command->line('╠════════════╬══════════╬════════╬══════════════════════════════════╣');
        $this->command->line('║ gold       ║ Gold     ║ 2000   ║ iPhone Trắng 256GB ×1 [IMEI]     ║');
        $this->command->line('║ .test      ║ (−5%)    ║        ║ iPad Air Bạc 256GB ×1            ║');
        $this->command->line('║            ║          ║        ║ Subtotal ≈ 52,980,000₫           ║');
        $this->command->line('╠════════════╩══════════╩════════╩══════════════════════════════════╣');
        $this->command->line('║  COUPON CÓ SẴN (gán sẵn cho tất cả 4 tài khoản)                  ║');
        $this->command->line('║  WELCOME10 → giảm 10%  (min: 0₫)                                 ║');
        $this->command->line('║  SALE20    → giảm 20%  (min: 10,000,000₫)                        ║');
        $this->command->line('║  GIAM500K  → giảm 500,000₫ cố định  (min: 5,000,000₫)            ║');
        $this->command->line('║  VIP2000K  → giảm 2,000,000₫ cố định (min: 20,000,000₫)          ║');
        $this->command->line('╠═══════════════════════════════════════════════════════════════════╣');
        $this->command->line('║  KỊCH BẢN TEST GỢI Ý                                             ║');
        $this->command->line('║  #1 customer.test + COD + không voucher  → 18,970,000₫           ║');
        $this->command->line('║  #2 customer.test + COD + GIAM500K       → 18,470,000₫           ║');
        $this->command->line('║  #3 bronze.test + SALE20                 → 36,776,000₫           ║');
        $this->command->line('║  #4 silver.test + WELCOME10 (sau CK 2%)  → 32,035,740₫           ║');
        $this->command->line('║  #5 silver.test + 100 điểm (=10,000₫)   → thêm −10,000₫         ║');
        $this->command->line('║  #6 gold.test + VIP2000K (sau CK 5%)    → 48,331,000₫           ║');
        $this->command->line('║  #7 gold.test + vnpay/momo (gateway)    → Đơn pending            ║');
        $this->command->line('╚═══════════════════════════════════════════════════════════════════╝');
        $this->command->newLine();
        $this->command->line('  URL đăng nhập: http://127.0.0.1:8000/login');
        $this->command->line('  Giỏ hàng:      http://127.0.0.1:8000/cart');
        $this->command->line('  Thanh toán:    http://127.0.0.1:8000/checkout');
    }
}

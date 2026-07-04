<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Tạo dữ liệu mẫu để test đầy đủ luồng giỏ hàng và thanh toán.
 *
 * Các tài khoản tạo ra:
 *  - bronze.test@gmail.com  (bronze, 0 điểm)       → test COD không có ưu đãi
 *  - silver.test@gmail.com  (silver 2%, 500 điểm)  → test chiết khấu hội viên + điểm
 *  - gold.test@gmail.com    (gold 5%, 2000 điểm)   → test tất cả ưu đãi cộng gộp
 *
 * Tất cả tài khoản đều dùng mật khẩu: Password@123
 *
 * Giỏ hàng mẫu gán cho từng tài khoản:
 *  - 1 sản phẩm loại quantity (Xiaomi Redmi Note 15 – 128GB Đen)
 *  - 1 sản phẩm loại imei/serial (iPhone 16 Pro – 128GB Titan Đen, qty=1)
 *
 * Coupon gán sẵn: WELCOME10, SALE20, GIAM500K, VIP2000K (phụ thuộc vào giá trị đơn)
 *
 * IMEI thêm đủ cho các variant imei/serial để checkout thành công.
 */
class CheckoutTestSeeder extends Seeder
{
    private const PASSWORD = 'Password@123';

    public function run(): void
    {
        $now = now();

        // ────────────────────────────────────────────────
        // 1. Tạo / cập nhật 3 user test với các hạng thành viên khác nhau
        // ────────────────────────────────────────────────
        $users = [
            [
                'email'            => 'bronze.test@gmail.com',
                'name'             => 'Khách hàng Bronze',
                'phone'            => '0911000001',
                'address'          => 'Số 10 Cầu Giấy, Hà Nội',
                'role'             => 'customer',
                'membership_level' => 'bronze',
                'points'           => 0,
                'total_spent'      => 0,
            ],
            [
                'email'            => 'silver.test@gmail.com',
                'name'             => 'Khách hàng Silver',
                'phone'            => '0911000002',
                'address'          => 'Số 20 Hoàng Mai, Hà Nội',
                'role'             => 'customer',
                'membership_level' => 'silver',
                'points'           => 500,
                'total_spent'      => 5000000,
            ],
            [
                'email'            => 'gold.test@gmail.com',
                'name'             => 'Khách hàng Gold',
                'phone'            => '0911000003',
                'address'          => 'Số 30 Thanh Xuân, Hà Nội',
                'role'             => 'customer',
                'membership_level' => 'gold',
                'points'           => 2000,
                'total_spent'      => 20000000,
            ],
        ];

        $createdUserIds = [];
        foreach ($users as $userData) {
            $email = $userData['email'];
            $payload = array_merge($userData, [
                'email_verified_at' => $now,
                'password'          => Hash::make(self::PASSWORD),
                'is_locked'         => false,
                'updated_at'        => $now,
            ]);
            unset($payload['email']);

            if (DB::table('users')->where('email', $email)->exists()) {
                DB::table('users')->where('email', $email)->update($payload);
                $id = DB::table('users')->where('email', $email)->value('id');
            } else {
                $payload['email']      = $email;
                $payload['created_at'] = $now;
                $id = DB::table('users')->insertGetId($payload);
            }

            $createdUserIds[$email] = $id;
        }

        $this->command->info('✓ Đã tạo/cập nhật 3 user test.');

        // ────────────────────────────────────────────────
        // 2. Lấy sản phẩm & variant cần dùng
        // ────────────────────────────────────────────────
        // quantity-based product
        $xiaomiProduct = DB::table('products')->where('slug', 'xiaomi-redmi-note-15')->first();
        // imei/serial product
        $iphoneProduct = DB::table('products')->where('slug', 'iphone-16-pro')->first();

        if (! $xiaomiProduct) {
            $this->command->warn('Không tìm thấy sản phẩm xiaomi-redmi-note-15. Chạy ProductSeeder trước.');
            return;
        }
        if (! $iphoneProduct) {
            $this->command->warn('Không tìm thấy sản phẩm iphone-16-pro. Chạy ProductSeeder trước.');
            return;
        }

        $xiaomiVariant = DB::table('product_variants')
            ->where('product_id', $xiaomiProduct->id)
            ->where('color', 'Đen')
            ->where('storage', '128GB')
            ->first();

        $iphoneVariant = DB::table('product_variants')
            ->where('product_id', $iphoneProduct->id)
            ->where('color', 'Titan Đen')
            ->where('storage', '128GB')
            ->first();

        if (! $xiaomiVariant) {
            $this->command->warn('Không tìm thấy variant Xiaomi Đen 128GB. Chạy ProductVariantSeeder trước.');
            return;
        }
        if (! $iphoneVariant) {
            $this->command->warn('Không tìm thấy variant iPhone Titan Đen 128GB. Chạy ProductVariantSeeder trước.');
            return;
        }

        $this->command->info('✓ Đã xác nhận sản phẩm và biến thể.');

        // ────────────────────────────────────────────────
        // 3. Đảm bảo đủ IMEI available cho iPhone variant
        //    (mỗi user cần 1 IMEI, tạo thêm 5 IMEI nếu thiếu)
        // ────────────────────────────────────────────────
        $availableImeiCount = DB::table('imeis')
            ->where('product_variant_id', $iphoneVariant->id)
            ->where('status', 'available')
            ->count();

        $imeiNeeded = count($users) + 2; // thêm dự phòng
        if ($availableImeiCount < $imeiNeeded) {
            $toCreate = $imeiNeeded - $availableImeiCount;
            $existingImeis = DB::table('imeis')->pluck('imei')->toArray();
            $created = 0;
            $suffix = 900000;
            while ($created < $toCreate) {
                $imei = 'TEST' . str_pad($suffix++, 11, '0', STR_PAD_LEFT);
                if (! in_array($imei, $existingImeis)) {
                    DB::table('imeis')->insert([
                        'product_variant_id' => $iphoneVariant->id,
                        'imei'               => $imei,
                        'status'             => 'available',
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ]);
                    $existingImeis[] = $imei;
                    $created++;
                }
            }
            $this->command->info("✓ Đã thêm {$created} IMEI cho iPhone variant.");
        }

        // ────────────────────────────────────────────────
        // 4. Đảm bảo stock_quantity đủ cho Xiaomi variant
        // ────────────────────────────────────────────────
        if ($xiaomiVariant->stock_quantity < 10) {
            DB::table('product_variants')
                ->where('id', $xiaomiVariant->id)
                ->update(['stock_quantity' => 30, 'updated_at' => $now]);
            $this->command->info('✓ Đã reset stock Xiaomi về 30.');
        }

        // ────────────────────────────────────────────────
        // 5. Gán giỏ hàng mẫu cho từng user test
        //    Xóa sạch giỏ cũ rồi tạo mới để đảm bảo nhất quán
        // ────────────────────────────────────────────────
        foreach ($createdUserIds as $email => $userId) {
            // Lấy hoặc tạo cart
            $cart = DB::table('carts')->where('user_id', $userId)->first();
            if ($cart) {
                // Xóa items cũ
                DB::table('cart_items')->where('cart_id', $cart->id)->delete();
                $cartId = $cart->id;
            } else {
                $cartId = DB::table('carts')->insertGetId([
                    'user_id'    => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Thêm sản phẩm quantity (Xiaomi – số lượng 2)
            DB::table('cart_items')->insert([
                'cart_id'            => $cartId,
                'product_id'         => $xiaomiProduct->id,
                'product_variant_id' => $xiaomiVariant->id,
                'quantity'           => 2,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

            // Thêm sản phẩm imei/serial (iPhone – số lượng 1)
            DB::table('cart_items')->insert([
                'cart_id'            => $cartId,
                'product_id'         => $iphoneProduct->id,
                'product_variant_id' => $iphoneVariant->id,
                'quantity'           => 1,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        $this->command->info('✓ Đã thiết lập giỏ hàng mẫu cho 3 user.');

        // ────────────────────────────────────────────────
        // 6. Gán coupon cho từng user test
        // ────────────────────────────────────────────────
        $couponCodes = ['WELCOME10', 'SALE20', 'GIAM500K', 'VIP2000K'];
        $coupons = DB::table('coupons')
            ->whereIn('code', $couponCodes)
            ->where('status', true)
            ->get();

        if ($coupons->isEmpty()) {
            $this->command->warn('Không tìm thấy coupon. Chạy CouponSeeder trước.');
        } else {
            foreach ($createdUserIds as $userId) {
                foreach ($coupons as $coupon) {
                    DB::table('coupon_user')->updateOrInsert(
                        ['coupon_id' => $coupon->id, 'user_id' => $userId],
                        ['created_at' => $now, 'updated_at' => $now]
                    );
                }
            }
            $this->command->info('✓ Đã gán coupon cho 3 user test.');
        }

        // ────────────────────────────────────────────────
        // 7. Ghi PointHistory ban đầu cho silver & gold
        // ────────────────────────────────────────────────
        $pointUsers = [
            'silver.test@gmail.com' => 500,
            'gold.test@gmail.com'   => 2000,
        ];

        foreach ($pointUsers as $email => $pts) {
            $uid = $createdUserIds[$email];
            $exists = DB::table('point_histories')
                ->where('user_id', $uid)
                ->where('type', 'initial')
                ->exists();

            if (! $exists) {
                DB::table('point_histories')->insert([
                    'user_id'     => $uid,
                    'points'      => $pts,
                    'type'        => 'purchase',
                    'description' => 'Điểm ban đầu – seeder',
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }

        $this->command->info('✓ Đã ghi lịch sử điểm ban đầu.');

        // ────────────────────────────────────────────────
        // 8. In tóm tắt để dev dễ tra cứu
        // ────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->line('════════════════════════════════════════════════════');
        $this->command->line(' DỮ LIỆU MẪU TEST GIỎ HÀNG & THANH TOÁN');
        $this->command->line('════════════════════════════════════════════════════');
        $this->command->line('');
        $this->command->line(' TÀI KHOẢN TEST (mật khẩu: Password@123)');
        $this->command->line(' ┌─────────────────────────────────────────────────');
        $this->command->line(' │ Email                   Hạng   Điểm   CK hội viên');
        $this->command->line(' ├─────────────────────────────────────────────────');
        $this->command->line(' │ bronze.test@gmail.com   Bronze     0   0%');
        $this->command->line(' │ silver.test@gmail.com   Silver   500   2%');
        $this->command->line(' │ gold.test@gmail.com     Gold    2000   5%');
        $this->command->line(' └─────────────────────────────────────────────────');
        $this->command->line('');
        $this->command->line(' GIỎ HÀNG (mỗi tài khoản)');
        $this->command->line(' ┌─────────────────────────────────────────────────');
        $this->command->line(' │ Xiaomi Redmi Note 15  128GB Đen   x2  =  13,980,000₫');
        $this->command->line(' │ iPhone 16 Pro         128GB Titan Đen  x1  =  29,990,000₫');
        $this->command->line(' │ Tổng subtotal:                      43,970,000₫');
        $this->command->line(' └─────────────────────────────────────────────────');
        $this->command->line('');
        $this->command->line(' COUPON CÓ SẴN');
        $this->command->line(' ┌────────────────────────────────────────────────────────');
        $this->command->line(' │ WELCOME10  Giảm 10%  Đơn tối thiểu: 0₫');
        $this->command->line(' │ SALE20     Giảm 20%  Đơn tối thiểu: 10,000,000₫');
        $this->command->line(' │ GIAM500K   Giảm cố định 500,000₫  Đơn tối thiểu: 5,000,000₫');
        $this->command->line(' │ VIP2000K   Giảm cố định 2,000,000₫ Đơn tối thiểu: 20,000,000₫');
        $this->command->line(' └────────────────────────────────────────────────────────');
        $this->command->line('');
        $this->command->line(' QUY TẮC ĐIỂM: 1 điểm = 100₫ khi dùng | Tích 1% giá trị đơn');
        $this->command->line('');
        $this->command->line(' KỊCH BẢN TEST GỢI Ý');
        $this->command->line(' ┌──────────────────────────────────────────────────────────────────');
        $this->command->line(' │ #1 Bronze + COD            → Subtotal 43,970,000₫');
        $this->command->line(' │ #2 Bronze + SALE20         → -8,794,000₫ → 35,176,000₫');
        $this->command->line(' │ #3 Bronze + VIP2000K + COD → -2,000,000₫ → 41,970,000₫');
        $this->command->line(' │ #4 Silver + SALE20 (2% off first)');
        $this->command->line(' │    Sau CK hội viên: 43,090,600₫ → SALE20 -8,618,120₫ → 34,472,480₫');
        $this->command->line(' │ #5 Gold + VIP2000K + 200 điểm (=20,000₫)');
        $this->command->line(' │    Sau CK 5%: 41,771,500₫ → -2,000,000₫ → -20,000₫ → 39,751,500₫');
        $this->command->line(' │ #6 Gateway (momo/vnpay)   → Tạo đơn pending, IMEI reserved');
        $this->command->line(' └──────────────────────────────────────────────────────────────────');
        $this->command->line('');
        $this->command->line(' PHƯƠNG THỨC THANH TOÁN HỖ TRỢ: cod, momo, vnpay, bank_transfer, card');
        $this->command->line('════════════════════════════════════════════════════');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class OrderFlowTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();

            $this->createProofImages();

            /*
            |--------------------------------------------------------------------------
            | 1. Xóa dữ liệu test cũ theo mã đơn ORD_FLOW_
            |--------------------------------------------------------------------------
            */
            $orderIds = DB::table('orders')
                ->where('order_code', 'like', 'ORD_FLOW_%')
                ->pluck('id')
                ->all();

            if (! empty($orderIds)) {
                $oldImeiIds = DB::table('order_items')
                    ->whereIn('order_id', $orderIds)
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

                $shipmentIds = DB::table('shipments')
                    ->whereIn('order_id', $orderIds)
                    ->pluck('id')
                    ->all();

                if (! empty($shipmentIds)) {
                    DB::table('shipment_items')
                        ->whereIn('shipment_id', $shipmentIds)
                        ->delete();
                }

                DB::table('order_receivers')
                    ->whereIn('order_id', $orderIds)
                    ->delete();

                DB::table('order_proofs')
                    ->whereIn('order_id', $orderIds)
                    ->delete();

                DB::table('shipments')
                    ->whereIn('order_id', $orderIds)
                    ->delete();

                DB::table('payments')
                    ->whereIn('order_id', $orderIds)
                    ->delete();

                DB::table('order_items')
                    ->whereIn('order_id', $orderIds)
                    ->delete();

                DB::table('orders')
                    ->whereIn('id', $orderIds)
                    ->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Xóa lại IMEI test riêng của seeder này
            |--------------------------------------------------------------------------
            */
            DB::table('imeis')
                ->where(function ($query) {
                    $query->where('imei', 'like', '86000100000%')
                        ->orWhere('imei', 'like', '86000200000%')
                        ->orWhere('imei', 'like', '86000300000%')
                        ->orWhere('imei', 'like', '86000400000%');
                })
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | 3. Tạo user admin và customer test
            |--------------------------------------------------------------------------
            */
            DB::table('users')->updateOrInsert(
                ['email' => 'admin@gmail.com'],
                [
                    'name' => 'Administrator',
                    'phone' => '0900000000',
                    'address' => 'Hà Nội',
                    'email_verified_at' => $now,
                    'password' => Hash::make('12345678'),
                    'points' => 0,
                    'role' => 'admin',
                    'total_spent' => 0,
                    'membership_level' => 'gold',
                    'remember_token' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('users')->updateOrInsert(
                ['email' => 'customer.flow@gmail.com'],
                [
                    'name' => 'Khách test flow',
                    'phone' => '0912345678',
                    'address' => '123 Nguyễn Trãi, Thanh Xuân, Hà Nội',
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

            $admin = DB::table('users')->where('email', 'admin@gmail.com')->first();
            $customer = DB::table('users')->where('email', 'customer.flow@gmail.com')->first();

            /*
            |--------------------------------------------------------------------------
            | 4. Tạo category, brand
            |--------------------------------------------------------------------------
            */
            DB::table('categories')->updateOrInsert(
                ['name' => 'Điện thoại'],
                [
                    'description' => 'Danh mục điện thoại',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('categories')->updateOrInsert(
                ['name' => 'Phụ kiện'],
                [
                    'description' => 'Danh mục phụ kiện',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('brands')->updateOrInsert(
                ['name' => 'Apple'],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('brands')->updateOrInsert(
                ['name' => 'Samsung'],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('brands')->updateOrInsert(
                ['name' => 'Anker'],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $phoneCategory = DB::table('categories')->where('name', 'Điện thoại')->first();
            $accessoryCategory = DB::table('categories')->where('name', 'Phụ kiện')->first();

            $apple = DB::table('brands')->where('name', 'Apple')->first();
            $samsung = DB::table('brands')->where('name', 'Samsung')->first();
            $anker = DB::table('brands')->where('name', 'Anker')->first();

            /*
            |--------------------------------------------------------------------------
            | 5. Tạo sản phẩm
            |--------------------------------------------------------------------------
            */
            $iphoneId = $this->upsertProduct([
                'category_id' => $phoneCategory->id,
                'brand_id' => $apple->id,
                'slug' => 'iphone-16-pro-flow',
                'name' => 'iPhone 16 Pro Flow Test',
                'description' => 'Điện thoại test flow xử lý đơn hàng',
                'price' => 29990000,
                'stock_quantity' => 0,
                'product_type' => 'imei/serial',
                'status' => true,
                'now' => $now,
            ]);

            $samsungId = $this->upsertProduct([
                'category_id' => $phoneCategory->id,
                'brand_id' => $samsung->id,
                'slug' => 'samsung-s25-flow',
                'name' => 'Samsung Galaxy S25 Flow Test',
                'description' => 'Điện thoại Samsung test flow',
                'price' => 24990000,
                'stock_quantity' => 0,
                'product_type' => 'imei/serial',
                'status' => true,
                'now' => $now,
            ]);

            $chargerId = $this->upsertProduct([
                'category_id' => $accessoryCategory->id,
                'brand_id' => $anker->id,
                'slug' => 'sac-nhanh-anker-flow',
                'name' => 'Sạc nhanh Anker Flow Test',
                'description' => 'Phụ kiện test quản lý tồn kho quantity',
                'price' => 490000,
                'stock_quantity' => 100,
                'product_type' => 'quantity',
                'status' => true,
                'now' => $now,
            ]);

            $caseId = $this->upsertProduct([
                'category_id' => $accessoryCategory->id,
                'brand_id' => $anker->id,
                'slug' => 'op-lung-flow',
                'name' => 'Ốp lưng Flow Test',
                'description' => 'Ốp lưng test quản lý tồn kho quantity',
                'price' => 190000,
                'stock_quantity' => 200,
                'product_type' => 'quantity',
                'status' => true,
                'now' => $now,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 6. Tạo biến thể
            |--------------------------------------------------------------------------
            */
            $iphoneBlack128 = $this->upsertVariant($iphoneId, 'Titan Đen', '128GB', 0, 10, $now);
            $iphoneWhite256 = $this->upsertVariant($iphoneId, 'Titan Trắng', '256GB', 3000000, 10, $now);

            $samsungBlack256 = $this->upsertVariant($samsungId, 'Đen', '256GB', 0, 10, $now);
            $samsungBlue512 = $this->upsertVariant($samsungId, 'Xanh', '512GB', 4000000, 10, $now);

            $chargerWhite = $this->upsertVariant($chargerId, 'Trắng', 'Standard', 0, 80, $now);
            $chargerBlack = $this->upsertVariant($chargerId, 'Đen', 'Standard', 0, 80, $now);

            $caseClear = $this->upsertVariant($caseId, 'Trong suốt', 'iPhone 16 Pro', 0, 100, $now);
            $caseBlack = $this->upsertVariant($caseId, 'Đen', 'Samsung S25', 0, 100, $now);

            /*
            |--------------------------------------------------------------------------
            | 7. Tạo IMEI available cho điện thoại
            |--------------------------------------------------------------------------
            */
            $this->createImeis($iphoneBlack128, '86000100000', 12, $now);
            $this->createImeis($iphoneWhite256, '86000200000', 12, $now);
            $this->createImeis($samsungBlack256, '86000300000', 12, $now);
            $this->createImeis($samsungBlue512, '86000400000', 12, $now);

            /*
            |--------------------------------------------------------------------------
            | 8. Tạo nhiều đơn hàng theo từng trạng thái để test admin
            |--------------------------------------------------------------------------
            */
            $this->createOrderCase([
                'code' => 'ORD_FLOW_001_PENDING_IMEI',
                'user_id' => $customer->id,
                'customer_name' => 'Nguyễn Văn Chờ Xử Lý',
                'customer_phone' => '0901000001',
                'shipping_address' => '10 Lê Lợi, Quận 1, TP.HCM',
                'receiver_name' => 'Người nhận đơn 001',
                'receiver_phone' => '0981000001',
                'receiver_address' => 'Lầu 1, 10 Lê Lợi, Quận 1, TP.HCM',
                'receiver_note' => 'Đơn mới, chưa xác nhận.',
                'status' => 'pending',
                'fulfillment_status' => 'pending',
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'items' => [
                    [
                        'product_id' => $iphoneId,
                        'variant_id' => $iphoneBlack128,
                        'price' => 29990000,
                        'quantity' => 1,
                        'assign_imei' => false,
                    ],
                ],
                'now' => $now,
            ]);

            $this->createOrderCase([
                'code' => 'ORD_FLOW_002_WAIT_PACK_MIXED',
                'user_id' => $customer->id,
                'customer_name' => 'Trần Thị Chờ Đóng Gói',
                'customer_phone' => '0901000002',
                'shipping_address' => '20 Cầu Giấy, Hà Nội',
                'receiver_name' => 'Người nhận đơn 002',
                'receiver_phone' => '0981000002',
                'receiver_address' => 'Tầng 2, 20 Cầu Giấy, Hà Nội',
                'receiver_note' => 'Đơn chờ đóng gói, cần chọn IMEI.',
                'status' => 'processing',
                'fulfillment_status' => 'waiting_pack',
                'confirmed_at' => $now->copy()->subHours(3),
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'items' => [
                    [
                        'product_id' => $iphoneId,
                        'variant_id' => $iphoneWhite256,
                        'price' => 32990000,
                        'quantity' => 1,
                        'assign_imei' => false,
                    ],
                    [
                        'product_id' => $chargerId,
                        'variant_id' => $chargerWhite,
                        'price' => 490000,
                        'quantity' => 2,
                    ],
                ],
                'now' => $now,
            ]);

            $this->createOrderCase([
                'code' => 'ORD_FLOW_003_WAIT_HANDOVER',
                'user_id' => $customer->id,
                'customer_name' => 'Lê Văn Chờ Bàn Giao',
                'customer_phone' => '0901000003',
                'shipping_address' => '30 Nguyễn Văn Linh, Đà Nẵng',
                'receiver_name' => 'Người nhận đơn 003',
                'receiver_phone' => '0981000003',
                'receiver_address' => '30 Nguyễn Văn Linh, Đà Nẵng',
                'receiver_note' => 'Đã đóng gói, chờ bàn giao shipper.',
                'status' => 'processing',
                'fulfillment_status' => 'waiting_handover',
                'confirmed_at' => $now->copy()->subHours(5),
                'packed_at' => $now->copy()->subHours(2),
                'payment_method' => 'bank_transfer',
                'payment_status' => 'paid',
                'items' => [
                    [
                        'product_id' => $samsungId,
                        'variant_id' => $samsungBlack256,
                        'price' => 24990000,
                        'quantity' => 1,
                        'assign_imei' => true,
                        'imei_status' => 'reserved',
                    ],
                ],
                'proofs' => ['packed'],
                'now' => $now,
                'created_by' => $admin->id,
            ]);

            $this->createOrderCase([
                'code' => 'ORD_FLOW_004_SHIPPING',
                'user_id' => $customer->id,
                'customer_name' => 'Phạm Văn Đang Giao',
                'customer_phone' => '0901000004',
                'shipping_address' => '40 Hai Bà Trưng, Hà Nội',
                'receiver_name' => 'Người nhận đơn 004',
                'receiver_phone' => '0981000004',
                'receiver_address' => '40 Hai Bà Trưng, Hà Nội',
                'receiver_note' => 'Đơn đang giao.',
                'status' => 'shipping',
                'fulfillment_status' => 'shipping',
                'confirmed_at' => $now->copy()->subDay(),
                'packed_at' => $now->copy()->subHours(20),
                'handed_over_at' => $now->copy()->subHours(10),
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'items' => [
                    [
                        'product_id' => $samsungId,
                        'variant_id' => $samsungBlue512,
                        'price' => 28990000,
                        'quantity' => 1,
                        'assign_imei' => true,
                        'imei_status' => 'reserved',
                    ],
                    [
                        'product_id' => $caseId,
                        'variant_id' => $caseBlack,
                        'price' => 190000,
                        'quantity' => 1,
                    ],
                ],
                'proofs' => ['packed'],
                'shipment_status' => 'shipping',
                'now' => $now,
                'created_by' => $admin->id,
            ]);

            $this->createOrderCase([
                'code' => 'ORD_FLOW_005_COMPLETED',
                'user_id' => $customer->id,
                'customer_name' => 'Hoàng Thị Hoàn Thành',
                'customer_phone' => '0901000005',
                'shipping_address' => '50 Trần Phú, Nha Trang',
                'receiver_name' => 'Người nhận đơn 005',
                'receiver_phone' => '0981000005',
                'receiver_address' => '50 Trần Phú, Nha Trang',
                'receiver_note' => 'Đã giao thành công.',
                'status' => 'completed',
                'fulfillment_status' => 'completed',
                'confirmed_at' => $now->copy()->subDays(3),
                'packed_at' => $now->copy()->subDays(2),
                'handed_over_at' => $now->copy()->subDays(2)->addHours(3),
                'delivered_at' => $now->copy()->subDay(),
                'payment_method' => 'vnpay',
                'payment_status' => 'paid',
                'items' => [
                    [
                        'product_id' => $iphoneId,
                        'variant_id' => $iphoneBlack128,
                        'price' => 29990000,
                        'quantity' => 1,
                        'assign_imei' => true,
                        'imei_status' => 'sold',
                    ],
                ],
                'proofs' => ['packed', 'delivered'],
                'shipment_status' => 'delivered',
                'now' => $now,
                'created_by' => $admin->id,
            ]);

            $this->createOrderCase([
                'code' => 'ORD_FLOW_006_FAILED',
                'user_id' => $customer->id,
                'customer_name' => 'Đỗ Văn Giao Thất Bại',
                'customer_phone' => '0901000006',
                'shipping_address' => '60 Phạm Văn Đồng, Thủ Đức',
                'receiver_name' => 'Người nhận đơn 006',
                'receiver_phone' => '0981000006',
                'receiver_address' => '60 Phạm Văn Đồng, Thủ Đức',
                'receiver_note' => 'Khách không nghe máy.',
                'status' => 'shipping',
                'fulfillment_status' => 'failed',
                'confirmed_at' => $now->copy()->subDays(2),
                'packed_at' => $now->copy()->subDays(2)->addHours(1),
                'handed_over_at' => $now->copy()->subDay(),
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'items' => [
                    [
                        'product_id' => $samsungId,
                        'variant_id' => $samsungBlack256,
                        'price' => 24990000,
                        'quantity' => 1,
                        'assign_imei' => true,
                        'imei_status' => 'reserved',
                    ],
                ],
                'proofs' => ['packed', 'failed_delivery'],
                'shipment_status' => 'failed',
                'now' => $now,
                'created_by' => $admin->id,
            ]);

            $this->createOrderCase([
                'code' => 'ORD_FLOW_007_CANCELLED',
                'user_id' => $customer->id,
                'customer_name' => 'Bùi Thị Đã Hủy',
                'customer_phone' => '0901000007',
                'shipping_address' => '70 Láng Hạ, Hà Nội',
                'receiver_name' => 'Người nhận đơn 007',
                'receiver_phone' => '0981000007',
                'receiver_address' => '70 Láng Hạ, Hà Nội',
                'receiver_note' => 'Đơn đã hủy, không giữ IMEI.',
                'status' => 'cancelled',
                'fulfillment_status' => 'cancelled',
                'cancelled_at' => $now->copy()->subHours(6),
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'items' => [
                    [
                        'product_id' => $iphoneId,
                        'variant_id' => $iphoneBlack128,
                        'price' => 29990000,
                        'quantity' => 1,
                        'assign_imei' => false,
                    ],
                ],
                'now' => $now,
            ]);

            $this->createOrderCase([
                'code' => 'ORD_FLOW_008_UNPAID',
                'user_id' => $customer->id,
                'customer_name' => 'Vũ Văn Chưa Thanh Toán',
                'customer_phone' => '0901000008',
                'shipping_address' => '80 Điện Biên Phủ, Bình Thạnh',
                'receiver_name' => 'Người nhận đơn 008',
                'receiver_phone' => '0981000008',
                'receiver_address' => '80 Điện Biên Phủ, Bình Thạnh',
                'receiver_note' => null,
                'status' => 'pending',
                'fulfillment_status' => 'pending',
                'payment_method' => 'bank_transfer',
                'payment_status' => 'pending',
                'items' => [
                    [
                        'product_id' => $caseId,
                        'variant_id' => $caseClear,
                        'price' => 190000,
                        'quantity' => 2,
                    ],
                ],
                'now' => $now,
            ]);

            $this->createOrderCase([
                'code' => 'ORD_FLOW_009_WAIT_PACK_QUANTITY',
                'user_id' => $customer->id,
                'customer_name' => 'Ngô Thị Phụ Kiện',
                'customer_phone' => '0901000009',
                'shipping_address' => '90 Hoàng Hoa Thám, Hà Nội',
                'receiver_name' => 'Người nhận đơn 009',
                'receiver_phone' => '0981000009',
                'receiver_address' => '90 Hoàng Hoa Thám, Hà Nội',
                'receiver_note' => 'Đơn chỉ có phụ kiện, không cần nhập IMEI.',
                'status' => 'processing',
                'fulfillment_status' => 'waiting_pack',
                'confirmed_at' => $now->copy()->subHour(),
                'payment_method' => 'momo',
                'payment_status' => 'paid',
                'items' => [
                    [
                        'product_id' => $chargerId,
                        'variant_id' => $chargerWhite,
                        'price' => 490000,
                        'quantity' => 1,
                    ],
                    [
                        'product_id' => $caseId,
                        'variant_id' => $caseClear,
                        'price' => 190000,
                        'quantity' => 1,
                    ],
                ],
                'now' => $now,
            ]);

            $this->command->info('Đã seed dữ liệu test flow đơn hàng theo flow mới thành công.');
            $this->command->info('Admin: admin@gmail.com / 12345678');
            $this->command->info('Customer: customer.flow@gmail.com / 12345678');
        });
    }

    private function upsertProduct(array $data): int
    {
        DB::table('products')->updateOrInsert(
            ['slug' => $data['slug']],
            [
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'stock_quantity' => $data['stock_quantity'],
                'product_type' => $data['product_type'],
                'status' => $data['status'],
                'created_at' => $data['now'],
                'updated_at' => $data['now'],
            ]
        );

        return (int) DB::table('products')
            ->where('slug', $data['slug'])
            ->value('id');
    }

    private function upsertVariant(
        int $productId,
        string $color,
        string $storage,
        int $additionalPrice,
        int $stockQuantity,
        $now
    ): int {
        DB::table('product_variants')->updateOrInsert(
            [
                'product_id' => $productId,
                'color' => $color,
                'storage' => $storage,
            ],
            [
                'additional_price' => $additionalPrice,
                'stock_quantity' => $stockQuantity,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return (int) DB::table('product_variants')
            ->where('product_id', $productId)
            ->where('color', $color)
            ->where('storage', $storage)
            ->value('id');
    }

    private function createImeis(int $variantId, string $prefix, int $count, $now): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $imei = $prefix . str_pad((string) $i, 4, '0', STR_PAD_LEFT);

            DB::table('imeis')->updateOrInsert(
                ['imei' => $imei],
                [
                    'product_variant_id' => $variantId,
                    'status' => 'available',
                    'reserved_at' => null,
                    'reserved_by_order_item_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function createOrderCase(array $data): void
    {
        $now = $data['now'];

        $subtotal = collect($data['items'])->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $data['user_id'],
            'order_code' => $data['code'],
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'shipping_address' => $data['shipping_address'],
            'subtotal' => $subtotal,
            'membership_discount' => 0,
            'coupon_discount' => 0,
            'total_amount' => $subtotal,
            'status' => $data['status'],
            'fulfillment_status' => $data['fulfillment_status'],
            'confirmed_at' => $data['confirmed_at'] ?? null,
            'packed_at' => $data['packed_at'] ?? null,
            'handed_over_at' => $data['handed_over_at'] ?? null,
            'delivered_at' => $data['delivered_at'] ?? null,
            'cancelled_at' => $data['cancelled_at'] ?? null,
            'shipping_label_printed_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('order_receivers')->insert([
            'order_id' => $orderId,
            'receiver_name' => $data['receiver_name'] ?? $data['customer_name'],
            'receiver_phone' => $data['receiver_phone'] ?? $data['customer_phone'],
            'receiver_address' => $data['receiver_address'] ?? $data['shipping_address'],
            'receiver_note' => $data['receiver_note'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($data['items'] as $item) {
            $product = DB::table('products')
                ->where('id', $item['product_id'])
                ->first();

            if (! $product) {
                throw new \Exception('Không tìm thấy sản phẩm ID: ' . $item['product_id']);
            }

            $imeiId = null;

            /*
            |--------------------------------------------------------------------------
            | Chỉ các case đã qua bước đóng gói mới gắn IMEI
            |--------------------------------------------------------------------------
            */
            if (
                $product->product_type === 'imei/serial'
                && ! empty($item['assign_imei'])
            ) {
                $imei = DB::table('imeis')
                    ->where('product_variant_id', $item['variant_id'])
                    ->where('status', 'available')
                    ->orderBy('id')
                    ->first();

                if (! $imei) {
                    throw new \Exception(
                        'Không còn IMEI available cho product_variant_id: ' . $item['variant_id']
                    );
                }

                $imeiId = $imei->id;
            }

            $orderItemId = DB::table('order_items')->insertGetId([
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['variant_id'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'total' => $item['price'] * $item['quantity'],
                'imei_id' => $imeiId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($imeiId) {
                $imeiStatus = $item['imei_status'] ?? 'reserved';

                DB::table('imeis')
                    ->where('id', $imeiId)
                    ->update([
                        'status' => $imeiStatus,
                        'reserved_at' => $imeiStatus === 'reserved' ? $now : null,
                        'reserved_by_order_item_id' => $imeiStatus === 'reserved'
                            ? $orderItemId
                            : null,
                        'updated_at' => $now,
                    ]);
            }

            if (
                $product->product_type === 'quantity'
                && $data['fulfillment_status'] !== 'cancelled'
            ) {
                DB::table('product_variants')
                    ->where('id', $item['variant_id'])
                    ->decrement('stock_quantity', $item['quantity']);
            }
        }

        DB::table('payments')->insert([
            'order_id' => $orderId,
            'payment_method' => $data['payment_method'],
            'amount' => $subtotal,
            'payment_status' => $data['payment_status'],
            'transaction_code' => $data['payment_status'] === 'paid'
                ? 'TXN_' . $data['code']
                : null,
            'paid_at' => $data['payment_status'] === 'paid' ? $now : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if (! empty($data['shipment_status'])) {
            DB::table('shipments')->insert([
                'order_id' => $orderId,
                'carrier_id' => null,
                'shipment_code' => 'SHP_' . $data['code'],
                'status' => $data['shipment_status'],
                'shipping_unit' => 'Shipper nội bộ',
                'tracking_code' => 'TRK_' . $data['code'],
                'shipping_status' => $data['shipment_status'],
                'service_type' => 'internal',
                'cost' => 30000,
                'tracking_url' => null,
                'metadata' => null,
                'requested_at' => $data['confirmed_at'] ?? $now,
                'picked_up_at' => $data['handed_over_at'] ?? null,
                'shipped_at' => $data['handed_over_at'] ?? null,
                'delivered_at' => $data['delivered_at'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (($data['proofs'] ?? []) as $proofType) {
            $imagePath = match ($proofType) {
                'packed' => 'order-proofs/demo-packed.png',
                'delivered' => 'order-proofs/demo-delivered.png',
                'failed_delivery' => 'order-proofs/demo-failed.png',
                default => 'order-proofs/demo-packed.png',
            };

            DB::table('order_proofs')->insert([
                'order_id' => $orderId,
                'type' => $proofType,
                'image_path' => $imagePath,
                'note' => match ($proofType) {
                    'packed' => 'Ảnh minh chứng đơn đã được đóng gói.',
                    'delivered' => 'Ảnh minh chứng đơn đã giao thành công.',
                    'failed_delivery' => 'Ảnh minh chứng giao hàng thất bại.',
                    default => null,
                },
                'created_by' => $data['created_by'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function createProofImages(): void
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAASwAAACWCAIAAADrOSKFAAABHUlEQVR4nO3SMQEAIAzAsIF/z0M'
            . 'SQUxNpmCz3QAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADwG4YAAaTtqfIAAAAASUVORK5CYII='
        );

        Storage::disk('public')->put('order-proofs/demo-packed.png', $png);
        Storage::disk('public')->put('order-proofs/demo-delivered.png', $png);
        Storage::disk('public')->put('order-proofs/demo-failed.png', $png);
    }
}

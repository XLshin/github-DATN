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
            | Đồng thời trả IMEI cũ về available nếu trước đó từng bị gắn sẵn
            |--------------------------------------------------------------------------
            */
            $oldOrders = DB::table('orders')
                ->where('order_code', 'like', 'ORD_REAL_TEST_%')
                ->get();

            foreach ($oldOrders as $oldOrder) {
                $oldOrderItemIds = DB::table('order_items')
                    ->where('order_id', $oldOrder->id)
                    ->pluck('id')
                    ->all();

                // Từ bản cập nhật hỗ trợ nhiều IMEI/order_item, nguồn liên kết chính là
                // imeis.reserved_by_order_item_id (1 order_item có thể có nhiều IMEI).
                // Cột order_items.imei_id cũ vẫn được dọn kèm để tương thích ngược với dữ liệu cũ.
                if (! empty($oldOrderItemIds)) {
                    DB::table('imeis')
                        ->whereIn('reserved_by_order_item_id', $oldOrderItemIds)
                        ->whereIn('status', ['reserved'])
                        ->update([
                            'status' => 'available',
                            'reserved_at' => null,
                            'reserved_by_order_item_id' => null,
                            'updated_at' => $now,
                        ]);
                }

                $oldImeiIds = DB::table('order_items')
                    ->where('order_id', $oldOrder->id)
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

                DB::table('order_receivers')
                    ->where('order_id', $oldOrder->id)
                    ->delete();

                DB::table('order_proofs')
                    ->where('order_id', $oldOrder->id)
                    ->delete();

                DB::table('shipment_items')
                    ->whereIn('shipment_id', function ($query) use ($oldOrder) {
                        $query->select('id')
                            ->from('shipments')
                            ->where('order_id', $oldOrder->id);
                    })
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
            | 3. Đảm bảo một số sản phẩm có đúng product_type để test
            |--------------------------------------------------------------------------
            */
            DB::table('products')
                ->where('slug', 'iphone-16-pro')
                ->update([
                    'product_type' => 'imei/serial',
                    'updated_at' => $now,
                ]);

            DB::table('products')
                ->whereIn('slug', [
                    'xiaomi-65w-charger',
                    'apple-airpods-pro-3',
                    'samsung-galaxy-buds-3',
                ])
                ->update([
                    'product_type' => 'quantity',
                    'updated_at' => $now,
                ]);

            /*
            |--------------------------------------------------------------------------
            | 4. Danh sách đơn test
            |--------------------------------------------------------------------------
            | Flow mới:
            | - Khi tạo đơn: order_items.imei_id luôn để null.
            | - Nếu sản phẩm là imei/serial thì admin nhập IMEI khi đóng gói.
            | - Nếu sản phẩm là quantity thì không cần nhập IMEI vẫn được in phiếu.
            */
            $testCases = [
                [
                    'order_code' => 'ORD_REAL_TEST_001',
                    'customer_name' => 'Khách Test Titan Đen 128GB',
                    'customer_phone' => '0911111101',
                    'shipping_address' => '101 Test Variant, Hà Nội',
                    'receiver_name' => 'Người nhận Titan Đen 128GB',
                    'receiver_phone' => '0988111101',
                    'receiver_address' => 'Phòng 101, Chung cư Test, Hà Nội',
                    'receiver_note' => 'Gọi trước khi giao.',
                    'payment_method' => 'cod',
                    'payment_status' => 'pending',
                    'items' => [
                        [
                            'product_slug' => 'iphone-16-pro',
                            'color' => 'Titan Đen',
                            'storage' => '128GB',
                            'quantity' => 1,
                        ],
                    ],
                ],
                [
                    'order_code' => 'ORD_REAL_TEST_002',
                    'customer_name' => 'Khách Test Titan Đen 256GB',
                    'customer_phone' => '0911111102',
                    'shipping_address' => '102 Test Variant, Hà Nội',
                    'receiver_name' => 'Người nhận Titan Đen 256GB',
                    'receiver_phone' => '0988111102',
                    'receiver_address' => 'Phòng 102, Chung cư Test, Hà Nội',
                    'receiver_note' => 'Giao giờ hành chính.',
                    'payment_method' => 'cod',
                    'payment_status' => 'pending',
                    'items' => [
                        [
                            'product_slug' => 'iphone-16-pro',
                            'color' => 'Titan Đen',
                            'storage' => '256GB',
                            'quantity' => 1,
                        ],
                    ],
                ],
                [
                    'order_code' => 'ORD_REAL_TEST_003',
                    'customer_name' => 'Khách Test Titan Trắng 128GB',
                    'customer_phone' => '0911111103',
                    'shipping_address' => '103 Test Variant, Hà Nội',
                    'receiver_name' => 'Người nhận Titan Trắng 128GB',
                    'receiver_phone' => '0988111103',
                    'receiver_address' => 'Phòng 103, Chung cư Test, Hà Nội',
                    'receiver_note' => null,
                    'payment_method' => 'cod',
                    'payment_status' => 'pending',
                    'items' => [
                        [
                            'product_slug' => 'iphone-16-pro',
                            'color' => 'Titan Trắng',
                            'storage' => '128GB',
                            'quantity' => 1,
                        ],
                    ],
                ],
                [
                    'order_code' => 'ORD_REAL_TEST_004',
                    'customer_name' => 'Khách Test Titan Trắng 256GB',
                    'customer_phone' => '0911111104',
                    'shipping_address' => '104 Test Variant, Hà Nội',
                    'receiver_name' => 'Người nhận Titan Trắng 256GB',
                    'receiver_phone' => '0988111104',
                    'receiver_address' => 'Phòng 104, Chung cư Test, Hà Nội',
                    'receiver_note' => 'Đã thanh toán online.',
                    'payment_method' => 'vnpay',
                    'payment_status' => 'paid',
                    'items' => [
                        [
                            'product_slug' => 'iphone-16-pro',
                            'color' => 'Titan Trắng',
                            'storage' => '256GB',
                            'quantity' => 1,
                        ],
                    ],
                ],
                [
                    'order_code' => 'ORD_REAL_TEST_005',
                    'customer_name' => 'Khách Test Titan Sa Mạc 128GB',
                    'customer_phone' => '0911111105',
                    'shipping_address' => '105 Test Variant, Hà Nội',
                    'receiver_name' => 'Người nhận Titan Sa Mạc 128GB',
                    'receiver_phone' => '0988111105',
                    'receiver_address' => 'Phòng 105, Chung cư Test, Hà Nội',
                    'receiver_note' => 'Giao buổi chiều.',
                    'payment_method' => 'momo',
                    'payment_status' => 'paid',
                    'items' => [
                        [
                            'product_slug' => 'iphone-16-pro',
                            'color' => 'Titan Sa Mạc',
                            'storage' => '128GB',
                            'quantity' => 1,
                        ],
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Case 6: Đơn chỉ có sản phẩm quantity, không cần IMEI
                |--------------------------------------------------------------------------
                */
                [
                    'order_code' => 'ORD_REAL_TEST_006_QUANTITY_ONLY',
                    'customer_name' => 'Khách Test Phụ Kiện',
                    'customer_phone' => '0911111106',
                    'shipping_address' => '106 Test Phụ Kiện, Hà Nội',
                    'receiver_name' => 'Người nhận Phụ Kiện',
                    'receiver_phone' => '0988111106',
                    'receiver_address' => 'Phòng 106, Chung cư Test, Hà Nội',
                    'receiver_note' => 'Đơn chỉ có phụ kiện, không cần nhập IMEI.',
                    'payment_method' => 'cod',
                    'payment_status' => 'pending',
                    'items' => [
                        [
                            'product_slug' => 'xiaomi-65w-charger',
                            'color' => 'Trắng',
                            'storage' => '65W',
                            'quantity' => 2,
                        ],
                        [
                            'product_slug' => 'apple-airpods-pro-3',
                            'color' => 'Trắng',
                            'storage' => '1 unit',
                            'quantity' => 1,
                        ],
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Case 7: Đơn mixed, có cả sản phẩm cần IMEI và không cần IMEI
                |--------------------------------------------------------------------------
                */
                [
                    'order_code' => 'ORD_REAL_TEST_007_MIXED',
                    'customer_name' => 'Khách Test Combo Điện Thoại Và Phụ Kiện',
                    'customer_phone' => '0911111107',
                    'shipping_address' => '107 Test Combo, Hà Nội',
                    'receiver_name' => 'Người nhận Combo',
                    'receiver_phone' => '0988111107',
                    'receiver_address' => 'Phòng 107, Chung cư Test, Hà Nội',
                    'receiver_note' => 'Đơn mixed: điện thoại cần IMEI, phụ kiện không cần IMEI.',
                    'payment_method' => 'vnpay',
                    'payment_status' => 'paid',
                    'items' => [
                        [
                            'product_slug' => 'iphone-16-pro',
                            'color' => 'Titan Đen',
                            'storage' => '128GB',
                            'quantity' => 1,
                        ],
                        [
                            'product_slug' => 'xiaomi-65w-charger',
                            'color' => 'Trắng',
                            'storage' => '65W',
                            'quantity' => 1,
                        ],
                        [
                            'product_slug' => 'samsung-galaxy-buds-3',
                            'color' => 'Đen',
                            'storage' => '1 unit',
                            'quantity' => 1,
                        ],
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Case 8: 1 dòng order_items IMEI với quantity > 1 (test tính năng gán NHIỀU
                | IMEI cho cùng 1 dòng order_items khi đóng gói).
                |--------------------------------------------------------------------------
                */
                [
                    'order_code' => 'ORD_REAL_TEST_008_MULTI_IMEI',
                    'customer_name' => 'Khách Test Mua 3 Máy Cùng Biến Thể',
                    'customer_phone' => '0911111108',
                    'shipping_address' => '108 Test Multi IMEI, Hà Nội',
                    'receiver_name' => 'Người nhận Multi IMEI',
                    'receiver_phone' => '0988111108',
                    'receiver_address' => 'Phòng 108, Chung cư Test, Hà Nội',
                    'receiver_note' => 'Đơn test 1 dòng order_items có quantity = 3, cần admin nhập đủ 3 IMEI khi đóng gói.',
                    'payment_method' => 'cod',
                    'payment_status' => 'pending',
                    'items' => [
                        [
                            'product_slug' => 'iphone-16-pro',
                            'color' => 'Titan Đen',
                            'storage' => '128GB',
                            'quantity' => 3,
                        ],
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Case 9: đơn mixed có dòng IMEI quantity > 1 xen với phụ kiện quantity > 1,
                | test admin nhập nhiều IMEI cho 1 dòng trong khi các dòng khác không cần IMEI.
                |--------------------------------------------------------------------------
                */
                [
                    'order_code' => 'ORD_REAL_TEST_009_MULTI_IMEI_MIXED',
                    'customer_name' => 'Khách Test Combo Nhiều Máy Và Phụ Kiện',
                    'customer_phone' => '0911111109',
                    'shipping_address' => '109 Test Multi IMEI Mixed, Hà Nội',
                    'receiver_name' => 'Người nhận Multi IMEI Mixed',
                    'receiver_phone' => '0988111109',
                    'receiver_address' => 'Phòng 109, Chung cư Test, Hà Nội',
                    'receiver_note' => 'Đơn mixed: 1 dòng điện thoại quantity = 2 cần 2 IMEI, phụ kiện quantity = 3 không cần IMEI.',
                    'payment_method' => 'momo',
                    'payment_status' => 'paid',
                    'items' => [
                        [
                            'product_slug' => 'iphone-16-pro',
                            'color' => 'Titan Trắng',
                            'storage' => '128GB',
                            'quantity' => 2,
                        ],
                        [
                            'product_slug' => 'xiaomi-65w-charger',
                            'color' => 'Trắng',
                            'storage' => '65W',
                            'quantity' => 3,
                        ],
                    ],
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | 5. Tạo từng đơn test
            |--------------------------------------------------------------------------
            */
            foreach ($testCases as $case) {
                $this->createRealOrder($customer->id, $case, $now);
            }

            $this->command->info('Đã tạo dữ liệu test đơn hàng theo flow mới.');
            $this->command->info('ORD_REAL_TEST_001 -> ORD_REAL_TEST_005: đơn chỉ có sản phẩm cần IMEI, quantity = 1.');
            $this->command->info('ORD_REAL_TEST_006_QUANTITY_ONLY: đơn chỉ có sản phẩm không cần IMEI.');
            $this->command->info('ORD_REAL_TEST_007_MIXED: đơn có cả sản phẩm cần IMEI và không cần IMEI.');
            $this->command->info('ORD_REAL_TEST_008_MULTI_IMEI: 1 dòng order_items IMEI, quantity = 3 -> test gán nhiều IMEI cho 1 dòng.');
            $this->command->info('ORD_REAL_TEST_009_MULTI_IMEI_MIXED: dòng IMEI quantity = 2 xen với phụ kiện quantity = 3.');
            $this->command->info('Customer: real.customer@gmail.com / 12345678');
        });
    }

    private function createRealOrder(int $customerId, array $case, $now): void
    {
        $items = [];
        $subtotal = 0;

        /*
        |--------------------------------------------------------------------------
        | 1. Lấy dữ liệu biến thể cho từng sản phẩm trong đơn
        |--------------------------------------------------------------------------
        */
        foreach ($case['items'] as $item) {
            $variantData = $this->getVariantData(
                productSlug: $item['product_slug'],
                color: $item['color'],
                storage: $item['storage']
            );

            if (! $variantData) {
                throw new \Exception(
                    'Không tìm thấy biến thể: '
                    . $item['product_slug']
                    . ' - '
                    . $item['color']
                    . ' - '
                    . $item['storage']
                );
            }

            $quantity = (int) ($item['quantity'] ?? 1);

            if ($quantity < 1) {
                throw new \Exception('Số lượng sản phẩm phải lớn hơn 0.');
            }

            /*
            |--------------------------------------------------------------------------
            | Từ bản cập nhật mới: 1 dòng order_items có thể cần NHIỀU IMEI (quantity > 1).
            | Admin sẽ nhập đủ `quantity` IMEI khi đóng gói, không còn giới hạn quantity = 1.
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | Nếu là sản phẩm cần IMEI thì kiểm tra kho có đủ số lượng IMEI available
            | tương ứng với quantity đã đặt. Không gắn IMEI ở bước tạo đơn.
            |--------------------------------------------------------------------------
            */
            if ($variantData->product_type === 'imei/serial') {
                $availableImeiCount = DB::table('imeis')
                    ->where('product_variant_id', $variantData->variant_id)
                    ->where('status', 'available')
                    ->count();

                if ($availableImeiCount < $quantity) {
                    throw new \Exception(
                        'Biến thể '
                        . $variantData->product_name . ' - '
                        . $variantData->color . ' - '
                        . $variantData->storage
                        . " chỉ còn {$availableImeiCount} IMEI available, không đủ cho quantity = {$quantity}."
                    );
                }
            }

            $price = (float) $variantData->product_price + (float) ($variantData->additional_price ?? 0);
            $total = $price * $quantity;

            $subtotal += $total;

            $items[] = [
                'product_id' => $variantData->product_id,
                'product_variant_id' => $variantData->variant_id,
                'product_name' => $variantData->product_name,
                'product_type' => $variantData->product_type,
                'price' => $price,
                'quantity' => $quantity,
                'total' => $total,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Tạo đơn hàng giống khách vừa đặt
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
        | 3. Tạo thông tin người nhận
        |--------------------------------------------------------------------------
        */
        DB::table('order_receivers')->insert([
            'order_id' => $orderId,
            'receiver_name' => $case['receiver_name'],
            'receiver_phone' => $case['receiver_phone'],
            'receiver_address' => $case['receiver_address'],
            'receiver_note' => $case['receiver_note'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 4. Tạo order_items nhưng KHÔNG gắn IMEI
        |--------------------------------------------------------------------------
        */
        foreach ($items as $item) {
            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'total' => $item['total'],
                // Cột imei_id vẫn còn trong DB nhưng app không còn dùng nữa (đã chuyển sang
                // liên kết nhiều-IMEI qua imeis.reserved_by_order_item_id). Để null cho đúng flow mới.
                'imei_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Tạo payment
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
            'Đã tạo '
            . $case['order_code']
            . ' | '
            . count($items)
            . ' sản phẩm | Chưa gắn IMEI'
        );
    }

    private function getVariantData(string $productSlug, string $color, string $storage)
    {
        return DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('products.status', true)
            ->where('product_variants.status', true)
            ->where('products.slug', $productSlug)
            ->where('product_variants.color', $color)
            ->where('product_variants.storage', $storage)
            ->select(
                'product_variants.id as variant_id',
                'product_variants.color',
                'product_variants.storage',
                'product_variants.additional_price',
                'products.id as product_id',
                'products.name as product_name',
                'products.price as product_price',
                'products.product_type'
            )
            ->first();
    }
}
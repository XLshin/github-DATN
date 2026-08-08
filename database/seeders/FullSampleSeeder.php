<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FullSampleSeeder extends Seeder
{
    private Carbon $now;

    public function run(): void
    {
        $this->now = now();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($this->seededTables() as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $users = $this->seedUsers();
        $catalog = $this->seedCatalog();
        $products = $this->seedProducts($catalog);
        $this->seedProductContent($products);
        $this->seedInventory($products);
        $commerce = $this->seedCommerce($users, $products);
        $this->seedAfterSales($users, $products, $commerce);
    }

    private function seededTables(): array
    {
        return [
            'warranty_media',
            'warranties',
            'shipment_items',
            'shipments',
            'order_proofs',
            'payments',
            'order_receivers',
            'order_items',
            'orders',
            'cart_items',
            'carts',
            'coupon_user',
            'point_histories',
            'reviews',
            'inventory_transactions',
            'imeis',
            'product_images',
            'product_specifications',
            'product_variants',
            'products',
            'product_groups',
            'brand_category',
            'banners',
            'coupons',
            'carriers',
            'addresses',
            'brands',
            'categories',
            'users',
        ];
    }

    private function seedUsers(): array
    {
        $users = [
            'admin' => $this->insert('users', [
                'name' => 'Admin Hasan',
                'email' => 'admin@bytezone.test',
                'phone' => '0909000001',
                'address' => 'Ha Noi',
                'email_verified_at' => $this->now,
                'password' => Hash::make('12345678'),
                'points' => 0,
                'role' => 'admin',
                'total_spent' => 0,
                'membership_level' => 'gold',
                'is_locked' => false,
            ]),
            'staff' => $this->insert('users', [
                'name' => 'Nhan Vien Kho',
                'email' => 'staff@bytezone.test',
                'phone' => '0909000002',
                'address' => 'Ha Noi',
                'email_verified_at' => $this->now,
                'password' => Hash::make('12345678'),
                'points' => 0,
                'role' => 'staff',
                'total_spent' => 0,
                'membership_level' => 'silver',
                'is_locked' => false,
            ]),
            'customer' => $this->insert('users', [
                'name' => 'Nguyen Hung',
                'email' => 'customer@bytezone.test',
                'phone' => '0985437245',
                'address' => '12 Cau Giay, Ha Noi',
                'email_verified_at' => $this->now,
                'password' => Hash::make('12345678'),
                'points' => 2500,
                'role' => 'customer',
                'total_spent' => 58400000,
                'membership_level' => 'gold',
                'is_locked' => false,
            ]),
            'customer2' => $this->insert('users', [
                'name' => 'Tran Minh Anh',
                'email' => 'minhanh@bytezone.test',
                'phone' => '0977000003',
                'address' => 'Quan 1, TP HCM',
                'email_verified_at' => $this->now,
                'password' => Hash::make('12345678'),
                'points' => 700,
                'role' => 'customer',
                'total_spent' => 8200000,
                'membership_level' => 'silver',
                'is_locked' => false,
            ]),
        ];

        if (Schema::hasTable('addresses')) {
            $this->insert('addresses', [
                'user_id' => $users['customer'],
                'label' => 'Nha rieng',
                'name' => 'Nguyen Hung',
                'phone' => '0985437245',
                'address_line' => '12 Cau Giay',
                'ward' => 'Dich Vong',
                'district' => 'Cau Giay',
                'city' => 'Ha Noi',
                'is_default' => true,
            ]);
            $this->insert('addresses', [
                'user_id' => $users['customer2'],
                'label' => 'Van phong',
                'name' => 'Tran Minh Anh',
                'phone' => '0977000003',
                'address_line' => '99 Nguyen Hue',
                'ward' => 'Ben Nghe',
                'district' => 'Quan 1',
                'city' => 'TP HCM',
                'is_default' => true,
            ]);
            $this->insert('addresses', [
                'user_id' => $users['admin'],
                'label' => 'Cua hang',
                'name' => 'Admin Hasan',
                'phone' => '0909000001',
                'address_line' => 'Byte Zone Store',
                'ward' => 'Dich Vong Hau',
                'district' => 'Cau Giay',
                'city' => 'Ha Noi',
                'is_default' => true,
            ]);
        }

        return $users;
    }

    private function seedCatalog(): array
    {
        $categories = [
            'phone' => $this->insert('categories', ['name' => 'Dien thoai', 'description' => 'Smartphone chinh hang']),
            'tablet' => $this->insert('categories', ['name' => 'Tablet', 'description' => 'May tinh bang va iPad']),
            'accessory' => $this->insert('categories', ['name' => 'Phu kien dien thoai', 'description' => 'Sac, cap, op lung, pin du phong']),
            'watch' => $this->insert('categories', ['name' => 'Dong ho thong minh', 'description' => 'Smartwatch va vong deo thong minh']),
        ];

        $brands = [
            'apple' => $this->insert('brands', ['name' => 'Apple', 'logo' => 'brands/apple.png', 'description' => 'Thuong hieu Apple']),
            'samsung' => $this->insert('brands', ['name' => 'Samsung', 'logo' => 'brands/samsung.png', 'description' => 'Thuong hieu Samsung']),
            'baseus' => $this->insert('brands', ['name' => 'Baseus', 'logo' => 'brands/baseus.png', 'description' => 'Phu kien Baseus']),
            'anker' => $this->insert('brands', ['name' => 'Anker', 'logo' => 'brands/anker.png', 'description' => 'Phu kien Anker']),
        ];

        foreach ([
            ['apple', 'phone'], ['apple', 'tablet'], ['apple', 'watch'],
            ['samsung', 'phone'], ['samsung', 'tablet'],
            ['baseus', 'accessory'], ['anker', 'accessory'],
        ] as [$brand, $category]) {
            $this->insert('brand_category', [
                'brand_id' => $brands[$brand],
                'category_id' => $categories[$category],
            ], false);
        }

        $this->insert('banners', [
            'title' => 'Sale iPhone chinh hang',
            'image' => 'banners/iphone-sale.jpg',
            'link' => '/products?category_id=' . $categories['phone'],
            'status' => true,
            'starts_at' => $this->now->copy()->subDay(),
            'ends_at' => $this->now->copy()->addMonth(),
        ]);
        $this->insert('banners', [
            'title' => 'Phu kien gia tot',
            'image' => 'banners/accessory-sale.jpg',
            'link' => '/products?category_id=' . $categories['accessory'],
            'status' => true,
            'starts_at' => $this->now->copy()->subDay(),
            'ends_at' => $this->now->copy()->addMonth(),
        ]);
        $this->insert('banners', [
            'title' => 'Tablet va dong ho moi',
            'image' => 'banners/tablet-watch.jpg',
            'link' => '/products?category_id=' . $categories['tablet'],
            'status' => true,
            'starts_at' => $this->now->copy()->subDay(),
            'ends_at' => $this->now->copy()->addMonth(),
        ]);

        return compact('categories', 'brands');
    }

    private function seedProducts(array $catalog): array
    {
        $c = $catalog['categories'];
        $b = $catalog['brands'];

        $groups = [
            'iphone' => $this->group($c['phone'], $b['apple'], 'iPhone 17 Pro Max', 'imei/serial'),
            'samsung' => $this->group($c['phone'], $b['samsung'], 'Samsung Galaxy S26 Ultra', 'imei/serial'),
            'ipad' => $this->group($c['tablet'], $b['apple'], 'iPad Air M3', 'imei/serial'),
            'charger' => $this->group($c['accessory'], $b['baseus'], 'Sac Baseus 30W', 'quantity'),
        ];

        $products = [
            'iphone256' => $this->product($groups['iphone'], $c['phone'], $b['apple'], 'iPhone 17 Pro Max 256GB', '256GB', 34990000, 'imei/serial'),
            'iphone512' => $this->product($groups['iphone'], $c['phone'], $b['apple'], 'iPhone 17 Pro Max 512GB', '512GB', 39990000, 'imei/serial'),
            'samsung256' => $this->product($groups['samsung'], $c['phone'], $b['samsung'], 'Samsung Galaxy S26 Ultra 256GB', '256GB', 29990000, 'imei/serial'),
            'charger30w' => $this->product($groups['charger'], $c['accessory'], $b['baseus'], 'Sac Baseus 30W Chinh Hang', null, 200000, 'quantity'),
        ];

        $products['variants'] = [
            'iphone256_black' => $this->variant($products['iphone256'], 'Titan Den', 0, 0, 'products/variants/iphone-black.jpg'),
            'iphone256_gold' => $this->variant($products['iphone256'], 'Titan Sa Mac', 300000, 0, 'products/variants/iphone-gold.jpg'),
            'iphone512_black' => $this->variant($products['iphone512'], 'Titan Den', 0, 0, 'products/variants/iphone-black.jpg'),
            'samsung256_gray' => $this->variant($products['samsung256'], 'Xam Titan', 0, 0, 'products/variants/samsung-gray.jpg'),
            'charger_black' => $this->variant($products['charger30w'], 'Den', 0, 20, 'products/variants/baseus-black.jpg'),
            'charger_white' => $this->variant($products['charger30w'], 'Trang', 0, 15, 'products/variants/baseus-white.jpg'),
        ];

        foreach ([$products['iphone256'], $products['iphone512'], $products['samsung256'], $products['charger30w']] as $productId) {
            DB::table('products')->where('id', $productId)->update([
                'stock_quantity' => DB::table('product_variants')->where('product_id', $productId)->sum('stock_quantity'),
            ]);
        }

        return ['groups' => $groups, 'products' => $products];
    }

    private function seedProductContent(array $data): void
    {
        foreach ($data['groups'] as $groupId) {
            foreach ([
                ['Tong quan', 'Bao hanh', '12 thang chinh hang', 1],
                ['Man hinh', 'Cong nghe', 'OLED / AMOLED tuy san pham', 2],
                ['Pin va sac', 'Sac nhanh', 'Ho tro sac nhanh', 3],
            ] as [$group, $name, $value, $sort]) {
                $this->insert('product_specifications', [
                    'product_group_id' => $groupId,
                    'group_name' => $group,
                    'name' => $name,
                    'value' => $value,
                    'sort_order' => $sort,
                ]);
            }
        }

        foreach ($data['products'] as $key => $productId) {
            if ($key === 'variants') {
                continue;
            }
            $this->insert('product_images', [
                'product_group_id' => null,
                'product_id' => $productId,
                'product_variant_id' => null,
                'image_path' => 'products/images/' . Str::slug((string) $key) . '-gallery.jpg',
            ]);
        }

        foreach ($data['products']['variants'] as $variantId) {
            $this->insert('product_images', [
                'product_group_id' => null,
                'product_id' => DB::table('product_variants')->where('id', $variantId)->value('product_id'),
                'product_variant_id' => $variantId,
                'image_path' => 'products/variants/gallery-' . $variantId . '.jpg',
            ]);
        }
    }

    private function seedInventory(array $data): void
    {
        $variants = $data['products']['variants'];

        $imeiRows = [
            ['iphone256_black', '356789012345001', 'available'],
            ['iphone256_black', '356789012345002', 'available'],
            ['iphone256_gold', '356789012345003', 'available'],
            ['iphone512_black', '356789012345004', 'sold'],
            ['samsung256_gray', '356789012345005', 'available'],
            ['samsung256_gray', '356789012345006', 'warranty'],
        ];

        foreach ($imeiRows as [$variantKey, $imei, $status]) {
            $this->insert('imeis', [
                'product_variant_id' => $variants[$variantKey],
                'imei' => $imei,
                'status' => $status,
                'reserved_at' => null,
                'reserved_by_order_item_id' => null,
            ]);
        }

        foreach ($variants as $variantId) {
            $quantity = (int) DB::table('product_variants')->where('id', $variantId)->value('stock_quantity');
            $this->insert('inventory_transactions', [
                'product_variant_id' => $variantId,
                'quantity' => max($quantity, 1),
                'type' => 'import',
                'note' => 'Nhap kho mau ban dau',
            ]);
        }
    }

    private function seedCommerce(array $users, array $data): array
    {
        $products = $data['products'];
        $variants = $products['variants'];

        $coupons = [
            'WELCOME10' => $this->insert('coupons', [
                'code' => 'WELCOME10',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'min_order_amount' => 1000000,
                'usage_limit' => 100,
                'used_count' => 1,
                'start_date' => $this->now->copy()->subDays(7),
                'end_date' => $this->now->copy()->addMonth(),
                'status' => true,
            ]),
            'PHUKIEN50K' => $this->insert('coupons', [
                'code' => 'PHUKIEN50K',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'min_order_amount' => 200000,
                'usage_limit' => 50,
                'used_count' => 0,
                'start_date' => $this->now->copy()->subDays(7),
                'end_date' => $this->now->copy()->addMonth(),
                'status' => true,
            ]),
            'FREESHIP30K' => $this->insert('coupons', [
                'code' => 'FREESHIP30K',
                'discount_type' => 'fixed',
                'discount_value' => 30000,
                'min_order_amount' => 500000,
                'usage_limit' => 80,
                'used_count' => 0,
                'start_date' => $this->now->copy()->subDays(7),
                'end_date' => $this->now->copy()->addMonth(),
                'status' => true,
            ]),
        ];

        foreach ([$coupons['WELCOME10'], $coupons['PHUKIEN50K']] as $couponId) {
            $this->insert('coupon_user', [
                'coupon_id' => $couponId,
                'user_id' => $users['customer'],
            ]);
        }
        $this->insert('coupon_user', [
            'coupon_id' => $coupons['FREESHIP30K'],
            'user_id' => $users['customer2'],
        ]);

        $cart = $this->insert('carts', ['user_id' => $users['customer']]);
        $this->insert('cart_items', [
            'cart_id' => $cart,
            'product_id' => $products['charger30w'],
            'product_variant_id' => $variants['charger_black'],
            'quantity' => 2,
        ]);
        $this->insert('cart_items', [
            'cart_id' => $cart,
            'product_id' => $products['iphone256'],
            'product_variant_id' => $variants['iphone256_black'],
            'quantity' => 1,
        ]);
        $cart2 = $this->insert('carts', ['user_id' => $users['customer2']]);
        $this->insert('cart_items', [
            'cart_id' => $cart2,
            'product_id' => $products['charger30w'],
            'product_variant_id' => $variants['charger_white'],
            'quantity' => 1,
        ]);
        $cart3 = $this->insert('carts', ['user_id' => $users['staff']]);
        $this->insert('cart_items', [
            'cart_id' => $cart3,
            'product_id' => $products['samsung256'],
            'product_variant_id' => $variants['samsung256_gray'],
            'quantity' => 1,
        ]);

        $orders = [
            'pending' => $this->order($users['customer'], 'ORD_SAMPLE_001', 'pending', 'pending', 34990000, $coupons['WELCOME10'], 'WELCOME10'),
            'pack' => $this->order($users['customer2'], 'ORD_SAMPLE_002', 'processing', 'waiting_pack', 200000, null, null),
            'done' => $this->order($users['customer'], 'ORD_SAMPLE_003', 'completed', 'completed', 39990000, null, null),
        ];

        $items = [];
        $items[] = $this->orderItem($orders['pending'], $products['iphone256'], $variants['iphone256_black'], 34990000, 1, null);
        $items[] = $this->orderItem($orders['pack'], $products['charger30w'], $variants['charger_black'], 200000, 1, null);
        $soldImei = DB::table('imeis')->where('imei', '356789012345004')->value('id');
        $items[] = $this->orderItem($orders['done'], $products['iphone512'], $variants['iphone512_black'], 39990000, 1, $soldImei);

        foreach ($orders as $key => $orderId) {
            $order = DB::table('orders')->where('id', $orderId)->first();
            $this->insert('order_receivers', [
                'order_id' => $orderId,
                'receiver_name' => $order->customer_name,
                'receiver_phone' => $order->customer_phone,
                'receiver_address' => $order->shipping_address,
                'receiver_note' => $key === 'pending' ? 'Giao gio hanh chinh' : null,
            ]);
            $this->insert('payments', [
                'order_id' => $orderId,
                'payment_method' => $key === 'done' ? 'vnpay' : 'cod',
                'amount' => $order->total_amount,
                'payment_status' => $key === 'done' ? 'paid' : 'pending',
                'transaction_code' => $key === 'done' ? 'VNPAY_SAMPLE_003' : null,
                'payer_name' => $order->customer_name,
                'payer_note' => null,
                'paid_at' => $key === 'done' ? $this->now->copy()->subDays(2) : null,
                'expires_at' => null,
            ]);
        }

        $carrier = $this->insert('carriers', [
            'name' => 'Giao Hang Nhanh',
            'code' => 'GHN',
            'api_credentials' => json_encode(['token' => 'sample-token']),
            'webhook_secret' => 'sample-secret',
            'active' => true,
        ]);
        $this->insert('carriers', [
            'name' => 'Giao Hang Tiet Kiem',
            'code' => 'GHTK',
            'api_credentials' => null,
            'webhook_secret' => null,
            'active' => true,
        ]);
        $carrier3 = $this->insert('carriers', [
            'name' => 'VNPost',
            'code' => 'VNPOST',
            'api_credentials' => null,
            'webhook_secret' => null,
            'active' => true,
        ]);

        $pendingShipment = $this->insert('shipments', [
            'order_id' => $orders['pending'],
            'shipping_unit' => 'VNPost',
            'tracking_code' => 'VNPOST001',
            'shipping_status' => 'pending',
            'carrier_id' => $carrier3,
            'shipment_code' => 'SHP_SAMPLE_001',
            'status' => 'pending',
            'cost' => 25000,
            'service_type' => 'standard',
            'tracking_url' => 'https://example.test/tracking/VNPOST001',
            'requested_at' => $this->now->copy()->subHours(5),
            'metadata' => json_encode(['sample' => true]),
        ]);
        $packShipment = $this->insert('shipments', [
            'order_id' => $orders['pack'],
            'shipping_unit' => 'GHTK',
            'tracking_code' => 'GHTK002',
            'shipping_status' => 'processing',
            'carrier_id' => null,
            'shipment_code' => 'SHP_SAMPLE_002',
            'status' => 'processing',
            'cost' => 20000,
            'service_type' => 'economy',
            'tracking_url' => 'https://example.test/tracking/GHTK002',
            'requested_at' => $this->now->copy()->subDay(),
            'metadata' => json_encode(['sample' => true]),
            'shipped_image' => 'shipments/packed-sample.jpg',
        ]);
        $doneShipment = $this->insert('shipments', [
            'order_id' => $orders['done'],
            'shipping_unit' => 'GHN',
            'tracking_code' => 'GHN123456',
            'shipping_status' => 'delivered',
            'carrier_id' => $carrier,
            'shipment_code' => 'SHP_SAMPLE_003',
            'status' => 'delivered',
            'cost' => 30000,
            'service_type' => 'standard',
            'tracking_url' => 'https://example.test/tracking/GHN123456',
            'requested_at' => $this->now->copy()->subDays(3),
            'picked_up_at' => $this->now->copy()->subDays(2),
            'shipped_at' => $this->now->copy()->subDays(2),
            'delivered_at' => $this->now->copy()->subDay(),
            'metadata' => json_encode(['sample' => true]),
            'shipped_image' => 'shipments/shipped-sample.jpg',
            'delivered_image' => 'shipments/delivered-sample.jpg',
        ]);
        $this->insert('shipment_items', [
            'shipment_id' => $pendingShipment,
            'order_item_id' => $items[0],
            'quantity' => 1,
        ]);
        $this->insert('shipment_items', [
            'shipment_id' => $packShipment,
            'order_item_id' => $items[1],
            'quantity' => 1,
        ]);
        $this->insert('shipment_items', [
            'shipment_id' => $doneShipment,
            'order_item_id' => $items[2],
            'quantity' => 1,
        ]);
        $this->insert('order_proofs', [
            'order_id' => $orders['pending'],
            'type' => 'packed',
            'image_path' => 'orders/proofs/packed-sample-001.jpg',
            'note' => 'Anh dong goi cho don cho xac nhan',
            'created_by' => $users['staff'],
        ]);
        $this->insert('order_proofs', [
            'order_id' => $orders['pack'],
            'type' => 'packed',
            'image_path' => 'orders/proofs/packed-sample-002.jpg',
            'note' => 'Anh dong goi don phu kien',
            'created_by' => $users['staff'],
        ]);
        $this->insert('order_proofs', [
            'order_id' => $orders['done'],
            'type' => 'delivered',
            'image_path' => 'orders/proofs/delivered-sample.jpg',
            'note' => 'Anh giao hang mau',
            'created_by' => $users['staff'],
        ]);

        return compact('orders', 'items');
    }

    private function seedAfterSales(array $users, array $data, array $commerce): void
    {
        $products = $data['products'];
        $variants = $products['variants'];

        $this->insert('reviews', [
            'user_id' => $users['customer'],
            'product_id' => $products['iphone512'],
            'rating' => 5,
            'comment' => 'May dep, hieu nang tot.',
            'status' => true,
        ]);
        $this->insert('reviews', [
            'user_id' => $users['customer2'],
            'product_id' => $products['charger30w'],
            'rating' => 4,
            'comment' => 'Sac nhanh, nho gon.',
            'status' => true,
        ]);
        $this->insert('reviews', [
            'user_id' => $users['customer'],
            'product_id' => $products['iphone256'],
            'rating' => 5,
            'comment' => 'Man hinh dep, cam giac cam chac tay.',
            'status' => true,
        ]);

        $this->insert('point_histories', [
            'user_id' => $users['customer'],
            'points' => 1000,
            'type' => 'purchase',
            'description' => 'Tich diem don hang mau',
        ]);
        $this->insert('point_histories', [
            'user_id' => $users['customer'],
            'points' => -300,
            'type' => 'usage',
            'description' => 'Doi diem don hang mau',
        ]);
        $this->insert('point_histories', [
            'user_id' => $users['customer2'],
            'points' => 500,
            'type' => 'purchase',
            'description' => 'Tich diem don phu kien mau',
        ]);

        $soldImei = DB::table('imeis')->where('imei', '356789012345004')->value('id');
        $activeWarranty = $this->insert('warranties', [
            'imei_id' => $soldImei,
            'order_id' => $commerce['orders']['done'],
            'warranty_start' => $this->now->copy()->subMonths(2)->toDateString(),
            'warranty_end' => $this->now->copy()->addMonths(10)->toDateString(),
            'status' => 'active',
            'customer_note' => 'Bao hanh mac dinh sau khi mua hang',
            'status_update_note' => null,
            'repair_result_note' => null,
            'customer_receipt_note' => null,
            'completed_at' => null,
        ]);
        $warrantyImei = DB::table('imeis')->where('imei', '356789012345006')->value('id');
        $claimedWarranty = $this->insert('warranties', [
            'imei_id' => $warrantyImei,
            'order_id' => $commerce['orders']['done'],
            'warranty_start' => $this->now->copy()->subMonth()->toDateString(),
            'warranty_end' => $this->now->copy()->addYear()->toDateString(),
            'status' => 'claimed',
            'customer_note' => 'May nong bat thuong',
            'status_update_note' => 'Da tiep nhan bao hanh',
            'repair_result_note' => 'Dang kiem tra',
            'customer_receipt_note' => 'Khach gui kem hop may',
            'completed_at' => null,
        ]);
        $expiredImei = DB::table('imeis')->where('imei', '356789012345003')->value('id');
        $expiredWarranty = $this->insert('warranties', [
            'imei_id' => $expiredImei,
            'order_id' => $commerce['orders']['done'],
            'warranty_start' => $this->now->copy()->subYears(2)->toDateString(),
            'warranty_end' => $this->now->copy()->subYear()->toDateString(),
            'status' => 'expired',
            'customer_note' => 'Bao hanh da het han',
            'status_update_note' => 'Kiem tra lich su bao hanh',
            'repair_result_note' => null,
            'customer_receipt_note' => null,
            'completed_at' => null,
        ]);
        $this->insert('warranty_media', [
            'warranty_id' => $activeWarranty,
            'stage' => 'reception',
            'type' => 'image',
            'file_path' => 'warranties/active-sample.jpg',
            'original_name' => 'active-sample.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 120000,
        ]);
        $this->insert('warranty_media', [
            'warranty_id' => $claimedWarranty,
            'stage' => 'reception',
            'type' => 'image',
            'file_path' => 'warranties/reception-sample.jpg',
            'original_name' => 'reception-sample.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 123456,
        ]);
        $this->insert('warranty_media', [
            'warranty_id' => $expiredWarranty,
            'stage' => 'completion',
            'type' => 'image',
            'file_path' => 'warranties/expired-sample.jpg',
            'original_name' => 'expired-sample.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 110000,
        ]);

        $this->insert('inventory_transactions', [
            'product_variant_id' => $variants['charger_black'],
            'quantity' => 1,
            'type' => 'export',
            'note' => 'Xuat kho don hang mau',
        ]);
    }

    private function group(int $categoryId, int $brandId, string $name, string $type): int
    {
        return $this->insert('product_groups', [
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Dong san pham mau ' . $name,
            'status' => true,
            'product_type' => $type,
        ]);
    }

    private function product(int $groupId, int $categoryId, int $brandId, string $name, ?string $storage, int $price, string $type): int
    {
        return $this->insert('products', [
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'product_group_id' => $groupId,
            'name' => $name,
            'storage' => $storage,
            'slug' => Str::slug($name),
            'description' => 'Mo ta mau cho ' . $name,
            'price' => $price,
            'stock_quantity' => 0,
            'thumbnail' => 'products/thumbnails/' . Str::slug($name) . '.jpg',
            'status' => true,
            'product_type' => $type,
        ]);
    }

    private function variant(int $productId, string $color, int $additionalPrice, int $stock, string $image): int
    {
        return $this->insert('product_variants', [
            'product_id' => $productId,
            'color' => $color,
            'image_path' => $image,
            'stock_quantity' => $stock,
            'additional_price' => $additionalPrice,
            'status' => true,
        ]);
    }

    private function order(int $userId, string $code, string $status, string $fulfillmentStatus, int $subtotal, ?int $couponId, ?string $couponCode): int
    {
        $couponDiscount = $couponCode === 'WELCOME10' ? 1000000 : 0;
        $total = max($subtotal - $couponDiscount, 0);

        return $this->insert('orders', [
            'user_id' => $userId,
            'order_code' => $code,
            'customer_name' => DB::table('users')->where('id', $userId)->value('name'),
            'customer_phone' => DB::table('users')->where('id', $userId)->value('phone'),
            'shipping_address' => DB::table('users')->where('id', $userId)->value('address') ?: 'Dia chi mau',
            'buyer_type' => 'self',
            'buyer_name' => null,
            'buyer_phone' => null,
            'subtotal' => $subtotal,
            'membership_discount' => 0,
            'coupon_discount' => $couponDiscount,
            'total_amount' => $total,
            'status' => $status,
            'fulfillment_status' => $fulfillmentStatus,
            'confirmed_at' => in_array($fulfillmentStatus, ['waiting_pack', 'completed'], true) ? $this->now->copy()->subDays(3) : null,
            'packed_at' => $fulfillmentStatus === 'completed' ? $this->now->copy()->subDays(2) : null,
            'handed_over_at' => $fulfillmentStatus === 'completed' ? $this->now->copy()->subDays(2) : null,
            'delivered_at' => $fulfillmentStatus === 'completed' ? $this->now->copy()->subDay() : null,
            'cancelled_at' => null,
            'cancel_reason' => null,
            'cancelled_by' => null,
            'shipping_label_printed_at' => $fulfillmentStatus === 'completed' ? $this->now->copy()->subDays(2) : null,
            'coupon_id' => $couponId,
            'coupon_code' => $couponCode,
            'points_used' => 0,
            'points_discount' => 0,
        ]);
    }

    private function orderItem(int $orderId, int $productId, int $variantId, int $price, int $quantity, ?int $imeiId): int
    {
        return $this->insert('order_items', [
            'order_id' => $orderId,
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'price' => $price,
            'quantity' => $quantity,
            'total' => $price * $quantity,
            'imei_id' => $imeiId,
        ]);
    }

    private function insert(string $table, array $data, bool $timestamps = true): int
    {
        if ($timestamps && Schema::hasColumn($table, 'created_at')) {
            $data['created_at'] ??= $this->now;
            $data['updated_at'] ??= $this->now;
        }

        return (int) DB::table($table)->insertGetId($data);
    }
}

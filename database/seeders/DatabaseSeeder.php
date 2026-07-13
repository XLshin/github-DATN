<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,

            CategorySeeder::class,
            BrandSeeder::class,

            ProductSeeder::class,
            ProductSpecificationSeeder::class,
            ProductVariantSeeder::class,
            TestCartProductSeeder::class,
            ProductImageSeeder::class,

            CouponSeeder::class,
            BannerSeeder::class,

            ImeiSeeder::class,

            /*
            |--------------------------------------------------------------------------
            | Đơn hàng mẫu cơ bản
            |--------------------------------------------------------------------------
            | ORD_TEST_SHIPMENT_001:
            | - Tạo đơn waiting_pack
            | - Không gắn IMEI sẵn
            | - Có thông tin người nhận
            */
            OrderSeeder::class,

            /*
            |--------------------------------------------------------------------------
            | Đơn hàng test giống đơn khách vừa đặt
            |--------------------------------------------------------------------------
            | ORD_REAL_TEST_001 -> ORD_REAL_TEST_005:
            | - Tạo đơn pending
            | - Có biến thể sản phẩm IMEI
            | - Không gắn IMEI sẵn
            | - Chờ admin xác nhận rồi đóng gói mới chọn IMEI
            */
            // RealOrderTestSeeder::class,

            /*
            |--------------------------------------------------------------------------
            | Đơn hàng test toàn bộ flow admin
            |--------------------------------------------------------------------------
            | ORD_FLOW_001 -> ORD_FLOW_008:
            | - pending: chưa có IMEI
            | - waiting_pack: chưa có IMEI
            | - waiting_handover: đã reserved IMEI
            | - shipping: đã reserved IMEI
            | - completed: IMEI sold
            | - failed: IMEI reserved
            | - cancelled: không giữ IMEI
            */
            // OrderFlowTestSeeder::class,

            ReviewSeeder::class,
            PointHistorySeeder::class,
            CouponUserSeeder::class,

            /*
            |--------------------------------------------------------------------------
            | Seeder bảo hành
            |--------------------------------------------------------------------------
            | Seeder này tự tạo đơn riêng ORD_TEST_WARRANTY_001 để chứa 4 IMEI test
            | (sold + warranty), không còn gắn vào đơn ORD_TEST_SHIPMENT_001 nữa.
            */
            WarrantyTestSeeder::class,
        ]);
    }
}

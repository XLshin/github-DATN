<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Thay hoàn toàn phương thức "vnpay" bằng "vietqr" (quét mã VietQR chuẩn, xác nhận qua
     * webhook ngân hàng SePay đã có sẵn) — đổi enum và cập nhật dữ liệu cũ (nếu có).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cod','card','bank_transfer','momo','vietqr','vnpay','zalopay','wallet') DEFAULT 'cod'");
        DB::statement("UPDATE payments SET payment_method = 'vietqr' WHERE payment_method = 'vnpay'");
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cod','card','bank_transfer','momo','vietqr','zalopay','wallet') DEFAULT 'cod'");

        DB::statement("ALTER TABLE wallet_topups MODIFY payment_method ENUM('bank_transfer','momo','vietqr','vnpay','card')");
        DB::statement("UPDATE wallet_topups SET payment_method = 'vietqr' WHERE payment_method = 'vnpay'");
        DB::statement("ALTER TABLE wallet_topups MODIFY payment_method ENUM('bank_transfer','momo','vietqr','card')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cod','card','bank_transfer','momo','vietqr','vnpay','zalopay','wallet') DEFAULT 'cod'");
        DB::statement("UPDATE payments SET payment_method = 'vnpay' WHERE payment_method = 'vietqr'");
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cod','card','bank_transfer','momo','vnpay','zalopay','wallet') DEFAULT 'cod'");

        DB::statement("ALTER TABLE wallet_topups MODIFY payment_method ENUM('bank_transfer','momo','vietqr','vnpay','card')");
        DB::statement("UPDATE wallet_topups SET payment_method = 'vnpay' WHERE payment_method = 'vietqr'");
        DB::statement("ALTER TABLE wallet_topups MODIFY payment_method ENUM('bank_transfer','momo','vnpay','card')");
    }
};

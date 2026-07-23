<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            // Mô phỏng ngân hàng xử lý xong lệnh hoàn tiền, dùng để tự động hoàn tiền cho các yêu
            // cầu hủy đơn trước khi giao và dưới ngưỡng tự động (đồ án — không kết nối ngân hàng thật).
            $table->timestamp('simulate_confirm_at')->nullable()->after('eligible_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            $table->dropColumn('simulate_confirm_at');
        });
    }
};

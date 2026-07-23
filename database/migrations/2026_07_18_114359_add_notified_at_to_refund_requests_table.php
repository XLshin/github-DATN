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
            // Thời điểm hệ thống mô phỏng gửi email + SMS báo hoàn tiền cho khách (đồ án — không
            // gọi nhà cung cấp email/SMS thật, chỉ ghi log và hiển thị trên trang chi tiết đơn).
            $table->timestamp('notified_at')->nullable()->after('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            $table->dropColumn('notified_at');
        });
    }
};

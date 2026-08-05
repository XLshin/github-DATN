<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Số lần khách đã thử thanh toán (kể cả lần đầu) — tối đa 2 lần, thất bại cả 2 thì tự hủy đơn.
            $table->unsignedTinyInteger('attempt_count')->default(1)->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('attempt_count');
        });
    }
};

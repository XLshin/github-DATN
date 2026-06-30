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
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('shipped_image')->nullable()->comment('Ảnh khi xuất kho');
            $table->string('delivered_image')->nullable()->comment('Ảnh khi giao hàng thành công');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn('shipped_image');
            $table->dropColumn('delivered_image');
        });
    }
};

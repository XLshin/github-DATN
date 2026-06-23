<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add reserved columns
        Schema::table('imeis', function (Blueprint $table) {
            $table->timestamp('reserved_at')->nullable()->after('status');
            $table->unsignedBigInteger('reserved_by_order_item_id')->nullable()->after('reserved_at');
        });

        // Add foreign key (nullable, null on delete)
        Schema::table('imeis', function (Blueprint $table) {
            $table->foreign('reserved_by_order_item_id')->references('id')->on('order_items')->nullOnDelete();
        });

        // Modify enum to include 'reserved' (use raw statement for MySQL)
        DB::statement("ALTER TABLE `imeis` MODIFY `status` ENUM('available','reserved','sold','warranty','returned') NOT NULL DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imeis', function (Blueprint $table) {
            $table->dropForeign(['reserved_by_order_item_id']);
            $table->dropColumn(['reserved_at', 'reserved_by_order_item_id']);
        });

        // revert enum to previous set
        DB::statement("ALTER TABLE `imeis` MODIFY `status` ENUM('available','sold','warranty','returned') NOT NULL DEFAULT 'available'");
    }
};

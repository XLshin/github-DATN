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
        // Add reserved columns if they don't exist (safe for SQLite/MySQL)
        $driver = Schema::getConnection()->getDriverName();

        if (! Schema::hasColumn('imeis', 'reserved_at') || ! Schema::hasColumn('imeis', 'reserved_by_order_item_id')) {
            Schema::table('imeis', function (Blueprint $table) use ($driver) {
                if (! Schema::hasColumn('imeis', 'reserved_at')) {
                    $table->timestamp('reserved_at')->nullable()->after('status');
                }
                if (! Schema::hasColumn('imeis', 'reserved_by_order_item_id')) {
                    $table->unsignedBigInteger('reserved_by_order_item_id')->nullable()->after('reserved_at');
                }
            });

            // Add foreign key only when running on databases that support it and when column exists
            if (Schema::hasColumn('imeis', 'reserved_by_order_item_id') && $driver !== 'sqlite') {
                Schema::table('imeis', function (Blueprint $table) {
                    $table->foreign('reserved_by_order_item_id')->references('id')->on('order_items')->nullOnDelete();
                });
            }
        }

        // Modify enum to include 'reserved' for drivers that support enum modifications (skip for sqlite)
        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE `imeis` MODIFY `status` ENUM('available','reserved','sold','warranty','returned') NOT NULL DEFAULT 'available'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('imeis', function (Blueprint $table) use ($driver) {
            if ($driver !== 'sqlite' && Schema::hasColumn('imeis', 'reserved_by_order_item_id')) {
                // drop foreign key if exists (MySQL)
                try {
                    $table->dropForeign(['reserved_by_order_item_id']);
                } catch (\Exception $e) {
                    // ignore if cannot drop
                }
            }

            if (Schema::hasColumn('imeis', 'reserved_at')) {
                $table->dropColumn('reserved_at');
            }
            if (Schema::hasColumn('imeis', 'reserved_by_order_item_id')) {
                $table->dropColumn('reserved_by_order_item_id');
            }
        });

        // revert enum to previous set for non-sqlite drivers
        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE `imeis` MODIFY `status` ENUM('available','sold','warranty','returned') NOT NULL DEFAULT 'available'");
        }
    }
};

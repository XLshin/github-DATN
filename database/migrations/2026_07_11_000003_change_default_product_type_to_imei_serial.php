<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('product_groups', 'product_type')) {
            DB::statement("ALTER TABLE product_groups MODIFY product_type ENUM('imei/serial', 'quantity') NOT NULL DEFAULT 'imei/serial'");
        }

        if (Schema::hasColumn('products', 'product_type')) {
            DB::statement("ALTER TABLE products MODIFY product_type ENUM('imei/serial', 'quantity') NOT NULL DEFAULT 'imei/serial'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_groups', 'product_type')) {
            DB::statement("ALTER TABLE product_groups MODIFY product_type ENUM('imei/serial', 'quantity') NOT NULL DEFAULT 'quantity'");
        }

        if (Schema::hasColumn('products', 'product_type')) {
            DB::statement("ALTER TABLE products MODIFY product_type ENUM('imei/serial', 'quantity') NOT NULL DEFAULT 'quantity'");
        }
    }
};

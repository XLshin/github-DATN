<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeProductTypes('quantity');

        if (Schema::hasColumn('product_groups', 'product_type')) {
            DB::statement("ALTER TABLE product_groups MODIFY product_type ENUM('imei/serial', 'quantity') NOT NULL DEFAULT 'quantity'");
        }

        if (Schema::hasColumn('products', 'product_type')) {
            DB::statement("ALTER TABLE products MODIFY product_type ENUM('imei/serial', 'quantity') NOT NULL DEFAULT 'quantity'");
        }
    }

    public function down(): void
    {
        $this->normalizeProductTypes('imei/serial');

        if (Schema::hasColumn('product_groups', 'product_type')) {
            DB::statement("ALTER TABLE product_groups MODIFY product_type ENUM('imei/serial', 'quantity') NOT NULL DEFAULT 'imei/serial'");
        }

        if (Schema::hasColumn('products', 'product_type')) {
            DB::statement("ALTER TABLE products MODIFY product_type ENUM('imei/serial', 'quantity') NOT NULL DEFAULT 'imei/serial'");
        }
    }

    private function normalizeProductTypes(string $default): void
    {
        if (Schema::hasColumn('product_groups', 'product_type')) {
            DB::table('product_groups')
                ->whereNull('product_type')
                ->orWhere('product_type', '')
                ->orWhereNotIn('product_type', ['imei/serial', 'quantity'])
                ->update(['product_type' => $default]);
        }

        if (Schema::hasColumn('products', 'product_type')) {
            DB::table('products')
                ->whereNull('product_type')
                ->orWhere('product_type', '')
                ->orWhereNotIn('product_type', ['imei/serial', 'quantity'])
                ->update(['product_type' => $default]);
        }
    }
};

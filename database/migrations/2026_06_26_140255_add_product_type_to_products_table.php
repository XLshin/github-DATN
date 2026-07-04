<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'product_type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->enum('product_type', ['imei/serial', 'quantity'])
                      ->default('quantity')
                      ->after('status');
            });
        }
    }

    public function down(): void
    {
        // Không drop product_type ở đây vì cột này có thể đã được tạo từ migration create_products_table.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('product_groups', 'product_type')) {
            Schema::table('product_groups', function (Blueprint $table) {
                $table->enum('product_type', ['imei/serial', 'quantity'])
                    ->default('quantity')
                    ->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_groups', 'product_type')) {
            Schema::table('product_groups', function (Blueprint $table) {
                $table->dropColumn('product_type');
            });
        }
    }
};

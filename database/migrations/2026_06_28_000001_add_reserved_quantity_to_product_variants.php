<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_variants')) {
            return;
        }

        if (! Schema::hasColumn('product_variants', 'reserved_quantity')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->integer('reserved_quantity')->default(0)->after('stock_quantity');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_variants')) {
            return;
        }

        if (Schema::hasColumn('product_variants', 'reserved_quantity')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn('reserved_quantity');
            });
        }
    }
};

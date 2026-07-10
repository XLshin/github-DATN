<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'product_group_id')) {
                $table->foreignId('product_group_id')
                    ->nullable()
                    ->after('brand_id')
                    ->constrained('product_groups')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('products', 'storage')) {
                $table->string('storage')
                    ->nullable()
                    ->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'product_group_id')) {
                $table->dropConstrainedForeignId('product_group_id');
            }

            if (Schema::hasColumn('products', 'storage')) {
                $table->dropColumn('storage');
            }
        });
    }
};

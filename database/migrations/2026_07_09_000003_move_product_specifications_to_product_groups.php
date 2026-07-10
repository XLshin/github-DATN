<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_specifications')) {
            return;
        }

        if (!Schema::hasColumn('product_specifications', 'product_group_id')) {
            Schema::table('product_specifications', function (Blueprint $table) {
                $table->foreignId('product_group_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('product_specifications', 'product_id')) {
            DB::statement('
                UPDATE product_specifications ps
                INNER JOIN products p ON p.id = ps.product_id
                SET ps.product_group_id = p.product_group_id
                WHERE ps.product_group_id IS NULL
            ');

            Schema::table('product_specifications', function (Blueprint $table) {
                try {
                    $table->dropForeign(['product_id']);
                } catch (Throwable $e) {
                    //
                }
            });

            Schema::table('product_specifications', function (Blueprint $table) {
                $table->dropColumn('product_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('product_specifications')) {
            return;
        }

        if (!Schema::hasColumn('product_specifications', 'product_id')) {
            Schema::table('product_specifications', function (Blueprint $table) {
                $table->foreignId('product_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('product_specifications', 'product_group_id')) {
            DB::statement('
                UPDATE product_specifications ps
                INNER JOIN products p ON p.product_group_id = ps.product_group_id
                SET ps.product_id = p.id
                WHERE ps.product_id IS NULL
            ');

            Schema::table('product_specifications', function (Blueprint $table) {
                try {
                    $table->dropForeign(['product_group_id']);
                } catch (Throwable $e) {
                    //
                }
            });

            Schema::table('product_specifications', function (Blueprint $table) {
                $table->dropColumn('product_group_id');
            });
        }
    }
};

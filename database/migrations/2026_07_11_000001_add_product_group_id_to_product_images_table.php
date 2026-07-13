<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->foreignId('product_group_id')
                ->nullable()
                ->after('id')
                ->constrained('product_groups')
                ->cascadeOnDelete();
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        DB::statement('ALTER TABLE product_images MODIFY product_id BIGINT UNSIGNED NULL');

        Schema::table('product_images', function (Blueprint $table) {
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropConstrainedForeignId('product_group_id');
        });

        DB::table('product_images')->whereNull('product_id')->delete();
        DB::statement('ALTER TABLE product_images MODIFY product_id BIGINT UNSIGNED NOT NULL');

        Schema::table('product_images', function (Blueprint $table) {
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });
    }
};

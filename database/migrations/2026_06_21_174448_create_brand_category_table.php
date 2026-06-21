<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unique(['brand_id', 'category_id']);
        });

        // Migrate dữ liệu cũ từ cột category_id sang bảng pivot
        if (Schema::hasColumn('brands', 'category_id')) {
            \DB::table('brands')
                ->whereNotNull('category_id')
                ->get()
                ->each(function ($brand) {
                    \DB::table('brand_category')->insertOrIgnore([
                        'brand_id'    => $brand->id,
                        'category_id' => $brand->category_id,
                    ]);
                });

            Schema::table('brands', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::dropIfExists('brand_category');
    }
};

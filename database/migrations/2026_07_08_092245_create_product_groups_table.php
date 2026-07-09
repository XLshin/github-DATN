<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('product_groups')) {
            return;
        }

        Schema::create('product_groups', function (Blueprint $table) {

            $table->id();

            // Danh mục
            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            // Thương hiệu
            $table->foreignId('brand_id')
                ->constrained()
                ->cascadeOnDelete();

            // Tên model
            $table->string('name');

            // slug
            $table->string('slug')->unique();

            // mô tả
            $table->text('description')->nullable();

            // trạng thái
            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_groups');
    }
};

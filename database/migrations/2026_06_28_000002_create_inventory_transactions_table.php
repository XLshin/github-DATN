<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_transactions')) {
            // Tạo mới bảng với đầy đủ schema hiện đại
            Schema::create('inventory_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_variant_id')->nullable();
                $table->integer('change_qty');
                $table->string('type');
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->unsignedBigInteger('performed_by')->nullable();
                $table->timestamps();
                $table->index('product_variant_id');
            });
        } else {
            // Bảng đã tồn tại từ migration cũ — thêm các cột còn thiếu
            Schema::table('inventory_transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('inventory_transactions', 'change_qty')) {
                    $table->integer('change_qty')->default(0)->after('id');
                }
                if (! Schema::hasColumn('inventory_transactions', 'reference_type')) {
                    $table->string('reference_type')->nullable();
                }
                if (! Schema::hasColumn('inventory_transactions', 'reference_id')) {
                    $table->unsignedBigInteger('reference_id')->nullable();
                }
                if (! Schema::hasColumn('inventory_transactions', 'performed_by')) {
                    $table->unsignedBigInteger('performed_by')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};

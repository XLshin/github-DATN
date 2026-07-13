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
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('buyer_type', ['self', 'proxy'])->default('self')->after('shipping_address');
            $table->string('buyer_name')->nullable()->after('buyer_type');
            $table->string('buyer_phone', 20)->nullable()->after('buyer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['buyer_type', 'buyer_name', 'buyer_phone']);
        });
    }
};

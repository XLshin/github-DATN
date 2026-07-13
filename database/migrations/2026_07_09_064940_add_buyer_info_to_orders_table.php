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
        if (! Schema::hasColumn('orders', 'buyer_type')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->enum('buyer_type', ['self', 'proxy'])->default('self')->after('shipping_address');
            });
        }

        if (! Schema::hasColumn('orders', 'buyer_name')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('buyer_name')->nullable()->after('buyer_type');
            });
        }

        if (! Schema::hasColumn('orders', 'buyer_phone')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('buyer_phone', 30)->nullable()->after('buyer_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = collect(['buyer_type', 'buyer_name', 'buyer_phone'])
                ->filter(fn ($column) => Schema::hasColumn('orders', $column))
                ->all();

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

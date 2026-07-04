<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('fulfillment_status', [
                'pending',
                'waiting_pack',
                'waiting_handover',
                'shipping',
                'completed',
                'cancelled',
                'failed'
            ])->default('pending')->after('status');

            $table->timestamp('confirmed_at')->nullable()->after('fulfillment_status');
            $table->timestamp('packed_at')->nullable()->after('confirmed_at');
            $table->timestamp('handed_over_at')->nullable()->after('packed_at');
            $table->timestamp('delivered_at')->nullable()->after('handed_over_at');
            $table->timestamp('cancelled_at')->nullable()->after('delivered_at');

            $table->timestamp('shipping_label_printed_at')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'fulfillment_status',
                'confirmed_at',
                'packed_at',
                'handed_over_at',
                'delivered_at',
                'cancelled_at',
                'shipping_label_printed_at',
            ]);
        });
    }
};
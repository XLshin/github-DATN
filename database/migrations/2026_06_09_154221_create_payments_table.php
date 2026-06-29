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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->enum('payment_method', [
                'cod',
                'card',
                'bank_transfer',
                'momo',
                'vnpay',
                'zalopay',
            ])->default('cod');

            $table->decimal('amount', 15, 2)->default(0);

            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'cancelled',
                'refunded',
            ])->default('pending');

            $table->string('transaction_code')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
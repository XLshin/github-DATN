<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_topups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 15, 2);

            $table->enum('payment_method', [
                'bank_transfer',
                'momo',
                'vnpay',
                'card',
            ]);

            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
            ])->default('pending');

            $table->string('transaction_code')->nullable();
            $table->string('payer_name')->nullable();
            $table->string('payer_note')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_topups');
    }
};

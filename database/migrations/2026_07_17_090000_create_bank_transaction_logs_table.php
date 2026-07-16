<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transaction_logs', function (Blueprint $table) {
            $table->id();

            // Loại giao dịch: nạp ví, rút tiền, hoàn tiền, thanh toán đơn hàng
            $table->enum('type', ['topup', 'withdrawal', 'refund', 'order_payment']);

            // Chiều tiền: in = tiền vào (khách/cửa hàng nhận), out = tiền ra (cửa hàng chuyển đi)
            $table->enum('direction', ['in', 'out']);

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Bản ghi gốc (WalletTopup, WalletWithdrawal, RefundRequest, Payment)
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');

            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable();

            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder_name')->nullable();

            $table->string('status');
            $table->string('transaction_code')->nullable();
            $table->string('proof_image')->nullable();

            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();

            $table->timestamp('occurred_at');

            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transaction_logs');
    }
};

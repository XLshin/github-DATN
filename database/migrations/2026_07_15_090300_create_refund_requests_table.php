<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('method', ['wallet', 'bank']);
            $table->decimal('amount', 15, 2);

            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'rejected',
            ])->default('pending');

            // Chỉ dùng khi method = bank
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();

            $table->timestamp('requested_at');
            // Thời điểm sớm nhất admin được phép hoàn tất (yêu cầu xử lý tối thiểu 7 ngày với hoàn qua ngân hàng)
            $table->timestamp('eligible_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->string('admin_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};

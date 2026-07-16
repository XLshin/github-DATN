<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_withdrawals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();

            // Lưu lại (snapshot) thông tin ngân hàng tại thời điểm yêu cầu để không đổi ngược khi
            // khách sửa/xóa bank_accounts sau này — đảm bảo tính bất biến phục vụ đối soát/pháp lý.
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('account_holder_name');

            $table->decimal('amount', 15, 2);

            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'rejected',
            ])->default('pending');

            $table->timestamp('requested_at');
            // Thời điểm sớm nhất admin được phép hoàn tất (yêu cầu xử lý tối thiểu 1 ngày làm việc)
            $table->timestamp('eligible_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->string('transaction_code')->nullable();

            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reject_reason')->nullable();
            $table->string('admin_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_withdrawals');
    }
};

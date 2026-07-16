<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('bank_name');
            $table->string('account_number');
            $table->string('account_holder_name');

            // Chỉ true khi tên chủ TK khớp tên tài khoản đăng ký (tự động) hoặc admin xác minh thủ công.
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_default')->default(false);

            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('proof_image')->nullable()->after('payer_note');
            $table->foreignId('confirmed_by')->nullable()->after('proof_image')->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->after('confirmed_by')->constrained('users')->nullOnDelete();
            $table->string('reject_reason')->nullable()->after('rejected_by');
            $table->string('admin_note')->nullable()->after('reject_reason');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('confirmed_by');
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn(['proof_image', 'reject_reason', 'admin_note']);
        });
    }
};

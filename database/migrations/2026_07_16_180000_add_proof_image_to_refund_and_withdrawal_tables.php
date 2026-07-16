<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            $table->string('proof_image')->nullable()->after('admin_note');
        });

        Schema::table('wallet_withdrawals', function (Blueprint $table) {
            $table->string('proof_image')->nullable()->after('admin_note');
        });
    }

    public function down(): void
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            $table->dropColumn('proof_image');
        });

        Schema::table('wallet_withdrawals', function (Blueprint $table) {
            $table->dropColumn('proof_image');
        });
    }
};

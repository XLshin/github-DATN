<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE wallet_transactions MODIFY type ENUM('topup','payment','refund','adjustment','withdrawal')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE wallet_transactions MODIFY type ENUM('topup','payment','refund','adjustment')");
    }
};

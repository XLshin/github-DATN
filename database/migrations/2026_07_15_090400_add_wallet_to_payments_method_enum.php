<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cod','card','bank_transfer','momo','vnpay','zalopay','wallet') DEFAULT 'cod'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cod','card','bank_transfer','momo','vnpay','zalopay') DEFAULT 'cod'");
    }
};

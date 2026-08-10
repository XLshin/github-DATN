<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `order_proofs` MODIFY `type` ENUM('packed','delivered','failed_delivery','cancelled') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `order_proofs` MODIFY `type` ENUM('packed','delivered','failed_delivery') NOT NULL");
    }
};

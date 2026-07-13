<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE products MODIFY description TEXT NULL');
    }

    public function down(): void
    {
        DB::table('products')
            ->whereNull('description')
            ->update(['description' => '']);

        DB::statement('ALTER TABLE products MODIFY description TEXT NOT NULL');
    }
};

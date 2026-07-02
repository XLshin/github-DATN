<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'is_locked')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_locked')->default(false)->after('membership_level');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_locked')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_locked');
            });
        }
    }
};

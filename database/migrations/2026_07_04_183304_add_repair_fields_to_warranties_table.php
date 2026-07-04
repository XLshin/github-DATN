<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            $table->text('status_update_note')->nullable()->after('customer_note');
            $table->text('repair_result_note')->nullable()->after('status_update_note');
            $table->timestamp('completed_at')->nullable()->after('repair_result_note');
        });
    }

    public function down(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            $table->dropColumn([
                'status_update_note',
                'repair_result_note',
                'completed_at',
            ]);
        });
    }
};
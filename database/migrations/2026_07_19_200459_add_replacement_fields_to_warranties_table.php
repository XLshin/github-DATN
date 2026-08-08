<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            $table->enum('fault_source', ['store', 'manufacturer', 'customer', 'unknown'])
                ->nullable()
                ->after('customer_note');
            $table->enum('resolution_type', ['repair', 'replace', 'reject'])
                ->nullable()
                ->after('fault_source');
            $table->foreignId('replacement_imei_id')
                ->nullable()
                ->after('resolution_type')
                ->constrained('imeis')
                ->nullOnDelete();
            $table->timestamp('replaced_at')
                ->nullable()
                ->after('replacement_imei_id');
        });
    }

    public function down(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            $table->dropForeign(['replacement_imei_id']);
            $table->dropColumn([
                'fault_source',
                'resolution_type',
                'replacement_imei_id',
                'replaced_at',
            ]);
        });
    }
};

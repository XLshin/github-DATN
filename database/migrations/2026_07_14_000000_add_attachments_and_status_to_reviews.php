<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('reviews', 'status')) {
                $table->boolean('status')->default(true)->after('comment');
            }

            if (! Schema::hasColumn('reviews', 'attachments')) {
                $table->json('attachments')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'attachments')) {
                $table->dropColumn('attachments');
            }

            if (Schema::hasColumn('reviews', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

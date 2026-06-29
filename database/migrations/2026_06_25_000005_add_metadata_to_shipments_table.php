<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('shipments')) {
            return;
        }

        if (! Schema::hasColumn('shipments', 'metadata')) {
            $connection = Schema::getConnection()->getDriverName();

            Schema::table('shipments', function (Blueprint $table) use ($connection) {
                if ($connection === 'sqlite') {
                    // SQLite doesn't have a native JSON type, store as text
                    $table->text('metadata')->nullable()->after('shipping_status');
                } else {
                    $table->json('metadata')->nullable()->after('shipping_status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasTable('shipments')) {
            return;
        }

        if (Schema::hasColumn('shipments', 'metadata')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });
        }
    }
};

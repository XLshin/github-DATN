<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    DB::statement("
        ALTER TABLE warranty_media
        MODIFY COLUMN stage ENUM(
            'reception',
            'completion',
            'customer_receipt'
        ) NOT NULL
    ");
}

public function down()
{
    DB::statement("
        ALTER TABLE warranty_media
        MODIFY COLUMN stage ENUM(
            'reception',
            'completion'
        ) NOT NULL
    ");
}
};

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

$output = [];
$db = $app->make('db');

try {
    if (!$db->connection()->getSchemaBuilder()->hasTable('coupon_user')) {
        $db->connection()->getSchemaBuilder()->create('coupon_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['coupon_id', 'user_id']);
        });
        $output[] = "coupon_user table created successfully";
    } else {
        $output[] = "coupon_user table already exists";
    }
} catch (\Exception $e) {
    $output[] = "ERROR: " . $e->getMessage();
}

file_put_contents('table_creation_log.txt', implode("\n", $output));
echo implode("\n", $output);


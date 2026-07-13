<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'users=' . App\Models\User::count() . PHP_EOL;
echo 'products=' . App\Models\Product::count() . PHP_EOL;

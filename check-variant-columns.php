<?php

use Illuminate\Support\Facades\DB;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check columns
$columns = DB::select('SHOW COLUMNS FROM product_variants');
echo "Columns in product_variants:\n";
foreach ($columns as $col) {
    echo "  - {$col->Field} ({$col->Type})\n";
}

echo "\n";

// Check iPhone 15 Plus variant
$variant = \App\Models\ProductVariant::whereHas('product', function($q) {
    $q->where('name', 'iPhone 15 Plus');
})->first();

if ($variant) {
    echo "Variant attributes:\n";
    $attrs = $variant->getAttributes();
    foreach ($attrs as $key => $value) {
        echo "  {$key}: {$value}\n";
    }
}

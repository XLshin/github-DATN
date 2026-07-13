<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$product = \App\Models\Product::where('name', 'iPhone 15 Plus')->first();
if ($product) {
    echo "Product Slug: {$product->slug}\n";
    echo "URL: http://127.0.0.1:8000/san-pham/{$product->slug}\n";
} else {
    echo "Product not found!\n";
}

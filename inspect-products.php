<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = \App\Models\Product::count();
$variants = \App\Models\ProductVariant::count();
$firstProduct = \App\Models\Product::with('variants')->first();

echo "products={$products}\n";
echo "variants={$variants}\n";
if ($firstProduct) {
    echo $firstProduct->name . ':' . $firstProduct->variants->count() . " variants\n";
}

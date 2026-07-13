<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Kiểm tra iPhone 15 Plus
$product = \App\Models\Product::where('name', 'iPhone 15 Plus')->first();
if ($product) {
    echo "Product: {$product->name}\n";
    echo "Product ID: {$product->id}\n";
    echo "Product Status: {$product->status}\n";
    echo "Product Total Stock: {$product->stock_quantity}\n";
    echo "Product Type: {$product->product_type}\n";
    echo "Variants:\n";

    $variants = $product->variants()->get();
    echo "  Total Variants: {$variants->count()}\n";
    foreach ($variants as $v) {
        echo "  - {$v->color}: stock={$v->stock_quantity}, price_add={$v->additional_price}, status={$v->status}\n";
    }

    $totalVariantStock = $product->variants()->sum('stock_quantity');
    echo "Total Variant Stock Sum: {$totalVariantStock}\n";
} else {
    echo "Product not found\n";
}

echo "\n---\n";
// Kiểm tra vài sản phẩm khác
$products = \App\Models\Product::limit(5)->get();
foreach ($products as $p) {
    $variantCount = $p->variants()->count();
    $variantStock = $p->variants()->sum('stock_quantity');
    echo "{$p->name}: status={$p->status}, stock_qty={$p->stock_quantity}, variants={$variantCount}, variant_stock={$variantStock}\n";
}

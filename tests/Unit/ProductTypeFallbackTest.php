<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductGroup;
use PHPUnit\Framework\TestCase;

class ProductTypeFallbackTest extends TestCase
{
    public function test_product_defaults_to_quantity_when_product_type_is_missing(): void
    {
        $product = new Product(['product_type' => null]);
        $this->assertSame('quantity', $product->product_type);

        $product = new Product(['product_type' => '']);
        $this->assertSame('quantity', $product->product_type);

        $product = new Product(['product_type' => 'invalid']);
        $this->assertSame('quantity', $product->product_type);
    }

    public function test_product_group_defaults_to_quantity_when_product_type_is_missing(): void
    {
        $group = new ProductGroup(['product_type' => null]);
        $this->assertSame('quantity', $group->product_type);

        $group = new ProductGroup(['product_type' => '']);
        $this->assertSame('quantity', $group->product_type);

        $group = new ProductGroup(['product_type' => 'invalid']);
        $this->assertSame('quantity', $group->product_type);
    }
}

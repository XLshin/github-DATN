<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id'       => Product::factory(),
            'color'            => fake()->colorName(),
            'storage'          => fake()->randomElement(['64GB', '128GB', '256GB']),
            'stock_quantity'   => fake()->numberBetween(1, 50),
            'additional_price' => 0,
            'status'           => true,
        ];
    }
}

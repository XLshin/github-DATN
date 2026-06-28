<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id'             => User::factory(),
            'order_code'          => 'ORD-' . strtoupper(fake()->unique()->bothify('??####')),
            'customer_name'       => fake()->name(),
            'customer_phone'      => fake()->numerify('0#########'),
            'shipping_address'    => fake()->address(),
            'subtotal'            => 1000000,
            'membership_discount' => 0,
            'coupon_discount'     => 0,
            'total_amount'        => 1000000,
            'status'              => 'pending',
        ];
    }
}

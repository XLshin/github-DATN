<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_checkout_with_db_cart(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create(['price' => 1000000, 'stock_quantity' => 10]);

        app(CartService::class)->addItem($user, $product, 2);

        $response = $this->actingAs($user)->post(route('checkout.process'), [
            'customer_name' => 'Nguyen Van A',
            'customer_phone' => '0912345678',
            'shipping_address' => '123 Test Street',
            'payment_method' => 'cod',
        ]);

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame($user->id, $order->user_id);
        $this->assertEquals(2000000, (float) $order->total_amount);
        $response->assertRedirect(route('checkout.success', $order->id));
        $this->assertTrue(app(CartService::class)->isEmpty($user));
    }

    public function test_guest_cannot_access_cart(): void
    {
        $this->get(route('cart.index'))->assertRedirect(route('login'));
    }
}

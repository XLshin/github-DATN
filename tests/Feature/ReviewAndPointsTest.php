<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewAndPointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_can_redeem_points_and_reduce_total(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'points' => 5000]);
        $product = Product::factory()->create(['price' => 1000000]);

        app(CartService::class)->addItem($user, $product, 1);

        $response = $this->actingAs($user)->post(route('checkout.process'), [
            'customer_name' => 'Nguyen Van A',
            'customer_phone' => '0912345678',
            'shipping_address' => '123 Test Street',
            'payment_method' => 'cod',
            'points_to_use' => 1000,
        ]);

        $order = Order::query()->first();

        $this->assertNotNull($order);
        $this->assertSame(1000, (int) $order->points_used);
        $this->assertSame(1000, (int) $order->points_discount);
        $this->assertSame(999000, (int) $order->total_amount);
        $this->assertSame(4999, (int) $user->fresh()->points);

        $response->assertRedirect(route('checkout.success', $order->id));
    }

    public function test_only_bought_users_can_submit_review(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $product), [
            'rating' => 5,
            'comment' => 'Great product',
        ]);

        $response->assertSessionHasErrors(['review']);
        $this->assertDatabaseMissing('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }
}

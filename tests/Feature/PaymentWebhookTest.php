<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Imei;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_success_with_valid_signature()
    {
        putenv('MOMO_SECRET=testsecret');

        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'P',
            'slug' => 'p',
            'description' => 'd',
            'price' => 100,
            'stock_quantity' => 10,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'c',
            'storage' => 's',
            'stock_quantity' => 10,
            'additional_price' => 0,
            'status' => 1,
        ]);

        Imei::create(['product_variant_id' => $variant->id, 'imei' => '999', 'status' => 'available']);

        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_code' => 'OC',
            'customer_name' => 'C',
            'customer_phone' => '0',
            'shipping_address' => 'a',
            'subtotal' => 100,
            'membership_discount' => 0,
            'coupon_discount' => 0,
            'total_amount' => 100,
            'status' => 'pending',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'price' => 100,
            'quantity' => 1,
            'total' => 100,
        ]);

        // Reserve imei
        Imei::where('product_variant_id', $variant->id)->first()->update([
            'status' => 'reserved',
            'reserved_by_order_item_id' => $item->id,
            'reserved_at' => now(),
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'momo',
            'amount' => 100,
            'payment_status' => 'pending',
            'transaction_code' => null,
            'paid_at' => null,
        ]);

        $payload = json_encode(['payment_id' => $payment->id, 'transaction_code' => 'tx123', 'status' => 'success']);
        $sig = hash_hmac('sha256', $payload, 'testsecret');

        $response = $this->withHeaders(['X-Signature' => $sig, 'X-Gateway' => 'momo'])
            ->postJson('/webhook/payment', json_decode($payload, true));

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'payment_status' => 'paid']);
        $this->assertDatabaseHas('imeis', ['status' => 'sold']);
    }

    public function test_webhook_invalid_signature_is_rejected()
    {
        putenv('MOMO_SECRET=testsecret');

        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_code' => 'OC2',
            'customer_name' => 'C',
            'customer_phone' => '0',
            'shipping_address' => 'a',
            'subtotal' => 50,
            'membership_discount' => 0,
            'coupon_discount' => 0,
            'total_amount' => 50,
            'status' => 'pending',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'momo',
            'amount' => 50,
            'payment_status' => 'pending',
            'transaction_code' => null,
            'paid_at' => null,
        ]);

        $payload = json_encode(['payment_id' => $payment->id, 'transaction_code' => 'tx123', 'status' => 'success']);
        $bad = 'bad_sig';

        $response = $this->withHeaders(['X-Signature' => $bad, 'X-Gateway' => 'momo'])
            ->postJson('/webhook/payment', json_decode($payload, true));

        $response->assertStatus(403);
    }
}

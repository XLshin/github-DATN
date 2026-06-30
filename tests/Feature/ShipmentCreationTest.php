<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Imei;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Carrier;
use App\Models\Shipment;

class ShipmentCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_webhook_finalizes_imei_and_creates_shipment()
    {
        putenv('MOMO_SECRET=testsecret');

        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Phone X',
            'slug' => 'phone-x',
            'description' => 'desc',
            'price' => 1000,
            'stock_quantity' => 5,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'black',
            'storage' => '128',
            'stock_quantity' => 5,
            'additional_price' => 0,
            'status' => 1,
        ]);

        // create imeis
        $imei = Imei::create(['product_variant_id' => $variant->id, 'imei' => 'IMEI123', 'status' => 'available']);

        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_code' => 'OC_TEST',
            'customer_name' => 'Test',
            'customer_phone' => '0123',
            'shipping_address' => 'Hanoi',
            'subtotal' => 1000,
            'membership_discount' => 0,
            'coupon_discount' => 0,
            'total_amount' => 1000,
            'status' => 'pending',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'price' => 1000,
            'quantity' => 1,
            'total' => 1000,
        ]);

        // reserve imei as CheckoutService would
        $imei->update(['status' => 'reserved', 'reserved_by_order_item_id' => $item->id, 'reserved_at' => now()]);

        // create carrier
        $carrier = Carrier::create(['name' => 'MockCarrier', 'code' => 'mock', 'api_credentials' => json_encode([]), 'active' => true]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'momo',
            'amount' => 1000,
            'payment_status' => 'pending',
            'transaction_code' => null,
            'paid_at' => null,
        ]);

        $payload = json_encode(['payment_id' => $payment->id, 'transaction_code' => 'tx999', 'status' => 'success']);
        $sig = hash_hmac('sha256', $payload, 'testsecret');

        $response = $this->withHeaders(['X-Signature' => $sig, 'X-Gateway' => 'momo'])
            ->postJson('/webhook/payment', json_decode($payload, true));

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'payment_status' => 'paid']);
        $this->assertDatabaseHas('imeis', ['imei' => 'IMEI123', 'status' => 'sold']);

        $this->assertDatabaseHas('shipments', ['order_id' => $order->id]);

        $shipment = Shipment::where('order_id', $order->id)->first();
        $this->assertNotNull($shipment);
        $this->assertTrue($shipment->carrier_id !== null || !empty($shipment->shipping_unit) || !empty($shipment->shipment_code));
    }
}

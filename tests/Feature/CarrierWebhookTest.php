<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Carrier;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\OrderItem;
use App\Models\Imei;
use App\Models\ProductVariant;
use App\Models\User;

class CarrierWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_carrier_webhook_delivered_updates_shipment_and_order()
    {
        $user = User::factory()->create(['email' => 'customer@example.test']);

        $order = Order::factory()->create(['user_id' => $user->id, 'order_code' => 'ORD_TEST_001', 'status' => 'shipping']);

        $carrier = Carrier::create(['name' => 'DemoCarrier', 'code' => 'demo', 'webhook_secret' => 'secret123', 'active' => true, 'api_credentials' => []]);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'carrier_id' => $carrier->id,
            'shipment_code' => 'CARRIER-DEL-1',
            'tracking_code' => 'CARRIER-DEL-1',
            'shipping_status' => 'shipping'
        ]);

        $payload = ['tracking_code' => $shipment->tracking_code, 'status' => 'delivered'];
        $signature = hash_hmac('sha256', json_encode($payload), $carrier->webhook_secret);

        $response = $this->postJson('/webhook/carrier/' . $carrier->code, $payload, ['X-Signature' => $signature]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('shipments', ['id' => $shipment->id, 'shipping_status' => 'delivered']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
    }

    public function test_carrier_webhook_failed_rolls_back_imei_and_stock()
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->create(['stock_quantity' => 5]);

        $order = Order::factory()->create(['user_id' => $user->id, 'order_code' => 'ORD_TEST_IMEI', 'status' => 'shipping']);

        $item = OrderItem::create(['order_id' => $order->id, 'product_id' => $variant->product_id ?? 1, 'product_variant_id' => $variant->id, 'quantity' => 1, 'price' => 1000, 'total' => 1000]);

        // create IMEI assigned/sold to item
        $imei = Imei::create(['product_variant_id' => $variant->id, 'imei' => 'IMEI-ROLL-1', 'status' => 'sold']);
        $item->imei_id = $imei->id;
        $item->save();

        // decrement stock to simulate it was reserved earlier
        $variant->stock_quantity = 4;
        $variant->save();

        $carrier = Carrier::create(['name' => 'DemoCarrier', 'code' => 'demo2', 'webhook_secret' => 'secret2', 'active' => true, 'api_credentials' => []]);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'carrier_id' => $carrier->id,
            'shipment_code' => 'CARRIER-FAIL-1',
            'tracking_code' => 'CARRIER-FAIL-1',
            'shipping_status' => 'shipping'
        ]);

        $payload = ['tracking_code' => $shipment->tracking_code, 'status' => 'failed'];
        $signature = hash_hmac('sha256', json_encode($payload), $carrier->webhook_secret);

        $response = $this->postJson('/webhook/carrier/' . $carrier->code, $payload, ['X-Signature' => $signature]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('shipments', ['id' => $shipment->id, 'shipping_status' => 'failed']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'returned']);

        $this->assertDatabaseHas('imeis', ['id' => $imei->id, 'status' => 'available']);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock_quantity' => 5]);
        $this->assertDatabaseMissing('order_items', ['id' => $item->id, 'imei_id' => $imei->id]);
    }
}

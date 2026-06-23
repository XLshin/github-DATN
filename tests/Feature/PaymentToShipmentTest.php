<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\Imei;
use App\Models\Carrier;

class PaymentToShipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_success_finalizes_imei_and_creates_shipment()
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->create(['stock_quantity' => 10]);

        $order = Order::factory()->create(['user_id' => $user->id, 'order_code' => 'ORD_PAY_001', 'status' => 'pending']);

        $item = OrderItem::create(['order_id' => $order->id, 'product_id' => $variant->product_id ?? 1, 'product_variant_id' => $variant->id, 'quantity' => 1, 'price' => 1000, 'total' => 1000]);

        // Create reserved IMEI for finalize path
        $reserved = Imei::create(['product_variant_id' => $variant->id, 'imei' => 'IMEI-RES-1', 'status' => 'reserved', 'reserved_by_order_item_id' => $item->id]);

        $payment = Payment::create(['order_id' => $order->id, 'payment_method' => 'momo', 'amount' => 1000, 'payment_status' => 'pending']);

        // create carrier to be selected
        $carrier = Carrier::create(['name' => 'AutoCarrier', 'code' => 'auto', 'active' => true, 'api_credentials' => []]);

        $payload = ['gateway' => 'momo', 'payment_id' => $payment->id, 'transaction_code' => 'TXN-1', 'status' => 'success'];
        // no secret configured for momo in test env, service allows

        $response = $this->postJson('/webhook/payment', $payload);

        $response->assertStatus(200);

        $payment->refresh();
        $order->refresh();

        $this->assertEquals('paid', $payment->payment_status);
        $this->assertEquals('processing', $order->status);

        // shipment should be created
        $this->assertDatabaseHas('shipments', ['order_id' => $order->id]);

        // reserved imei should be finalized to sold and attached to order item
        $this->assertDatabaseHas('imeis', ['id' => $reserved->id, 'status' => 'sold']);
        $this->assertDatabaseHas('order_items', ['id' => $item->id, 'imei_id' => $reserved->id]);
    }
}

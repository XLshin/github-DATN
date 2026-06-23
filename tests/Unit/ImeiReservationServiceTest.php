<?php

namespace Tests\Unit;

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
use App\Services\ImeiReservationService;

class ImeiReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reserve_success()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'desc',
            'price' => 100,
            'stock_quantity' => 10,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'black',
            'storage' => '64GB',
            'stock_quantity' => 10,
            'additional_price' => 0,
            'status' => 1,
        ]);

        // create 3 imeis
        Imei::create(['product_variant_id' => $variant->id, 'imei' => '111111111111111', 'status' => 'available']);
        Imei::create(['product_variant_id' => $variant->id, 'imei' => '222222222222222', 'status' => 'available']);
        Imei::create(['product_variant_id' => $variant->id, 'imei' => '333333333333333', 'status' => 'available']);

        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_code' => 'ORDER1',
            'customer_name' => 'Customer',
            'customer_phone' => '0123456789',
            'shipping_address' => 'addr',
            'subtotal' => 200,
            'membership_discount' => 0,
            'coupon_discount' => 0,
            'total_amount' => 200,
            'status' => 'pending',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'price' => 100,
            'quantity' => 2,
            'total' => 200,
        ]);

        $service = new ImeiReservationService();

        $this->assertTrue($service->reserve($order));

        $reserved = Imei::where('status', 'reserved')->get();
        $this->assertCount(2, $reserved);
        $this->assertEquals($item->id, $reserved->first()->reserved_by_order_item_id);
    }

    public function test_reserve_insufficient()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Test Product 2',
            'slug' => 'test-product-2',
            'description' => 'desc',
            'price' => 100,
            'stock_quantity' => 1,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'white',
            'storage' => '128GB',
            'stock_quantity' => 1,
            'additional_price' => 0,
            'status' => 1,
        ]);

        Imei::create(['product_variant_id' => $variant->id, 'imei' => '444444444444444', 'status' => 'available']);

        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_code' => 'ORDER2',
            'customer_name' => 'Customer',
            'customer_phone' => '0123456789',
            'shipping_address' => 'addr',
            'subtotal' => 300,
            'membership_discount' => 0,
            'coupon_discount' => 0,
            'total_amount' => 300,
            'status' => 'pending',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'price' => 100,
            'quantity' => 2,
            'total' => 200,
        ]);

        $service = new ImeiReservationService();

        $this->assertFalse($service->reserve($order));

        $reserved = Imei::where('status', 'reserved')->get();
        $this->assertCount(0, $reserved);
    }

    public function test_finalize_assigns_and_sets_sold()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Test Product 3',
            'slug' => 'test-product-3',
            'description' => 'desc',
            'price' => 100,
            'stock_quantity' => 10,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'blue',
            'storage' => '64GB',
            'stock_quantity' => 10,
            'additional_price' => 0,
            'status' => 1,
        ]);

        Imei::create(['product_variant_id' => $variant->id, 'imei' => '555555555555555', 'status' => 'available']);
        Imei::create(['product_variant_id' => $variant->id, 'imei' => '666666666666666', 'status' => 'available']);

        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_code' => 'ORDER3',
            'customer_name' => 'Customer',
            'customer_phone' => '0123456789',
            'shipping_address' => 'addr',
            'subtotal' => 200,
            'membership_discount' => 0,
            'coupon_discount' => 0,
            'total_amount' => 200,
            'status' => 'pending',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'price' => 100,
            'quantity' => 2,
            'total' => 200,
        ]);

        $service = new ImeiReservationService();

        $this->assertTrue($service->reserve($order));

        $service->finalize($order);

        $sold = Imei::where('status', 'sold')->get();
        $this->assertCount(2, $sold);

        $this->assertNotNull(OrderItem::find($item->id)->imei_id);
    }
}

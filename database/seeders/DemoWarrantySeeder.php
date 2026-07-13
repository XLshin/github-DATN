<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Imei;
use App\Models\Warranty;
use Illuminate\Support\Str;

class DemoWarrantySeeder extends Seeder
{
    public function run(): void
    {
        // Create or get user Xuân Bắc
        $user = User::firstOrCreate(
            ['email' => 'xuanbac@example.com'],
            [
                'name' => 'Xuân Bắc',
                'password' => bcrypt('password'),
                'phone' => '0123456789',
            ]
        );

        // Create a product if none
        $product = Product::first();
        if (! $product) {
            $product = Product::create([
                'name' => 'Demo Phone',
                'slug' => 'demo-phone',
                'price' => 1000000,
                'description' => 'Sản phẩm demo',
            ]);
        }

        // Create variant
        $variant = ProductVariant::where('product_id', $product->id)->first();
        if (! $variant) {
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'color' => 'Black',
                'storage' => '128GB',
                'additional_price' => 0,
            ]);
        }

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'order_code' => 'DEMO' . time(),
            'customer_name' => $user->name,
            'customer_phone' => $user->phone ?? '0123456789',
            'shipping_address' => 'Địa chỉ demo',
            'subtotal' => $product->price,
            'total_amount' => $product->price,
            'status' => 'completed',
            'fulfillment_status' => 'completed',
        ]);

        // Create IMEI
        $imeiCode = '359' . rand(10000000, 99999999);
        $imei = Imei::create([
            'product_variant_id' => $variant->id,
            'imei' => $imeiCode,
            'status' => 'sold',
        ]);

        // Create order item linked to IMEI
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'price' => $product->price,
            'quantity' => 1,
            'imei_id' => $imei->id,
        ]);

        // Create warranty
        $warranty = Warranty::create([
            'imei_id' => $imei->id,
            'order_id' => $order->id,
            'warranty_start' => now()->toDateString(),
            'warranty_end' => now()->addYear()->toDateString(),
            'status' => Warranty::STATUS_CLAIMED,
            'customer_note' => 'Máy không lên nguồn',
        ]);

        $this->command->info('Demo data created:');
        $this->command->info('User: xuanbac@example.com / password');
        $this->command->info('IMEI: ' . $imeiCode);
        $this->command->info('Warranty id: ' . $warranty->id);
    }
}

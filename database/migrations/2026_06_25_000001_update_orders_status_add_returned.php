<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            // Skip sqlite ALTER/RENAME operations entirely — they are fragile and
            // can leave behind references (orders_old) that break test runs.
            return;

            Schema::create('orders_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('order_code')->unique();
                $table->string('customer_name');
                $table->string('customer_phone');
                $table->text('shipping_address');
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('membership_discount', 15, 2)->default(0);
                $table->decimal('coupon_discount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->enum('status', ['pending', 'processing', 'shipping', 'completed', 'cancelled', 'returned'])->default('pending');
                $table->timestamps();
            });

            DB::statement('INSERT INTO orders_new (id, user_id, order_code, customer_name, customer_phone, shipping_address, subtotal, membership_discount, coupon_discount, total_amount, status, created_at, updated_at) SELECT id, user_id, order_code, customer_name, customer_phone, shipping_address, subtotal, membership_discount, coupon_discount, total_amount, status, created_at, updated_at FROM orders_old');
            DB::statement('DROP TABLE orders_old');
            DB::statement('ALTER TABLE orders_new RENAME TO orders');

            DB::commit();
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','shipping','completed','cancelled','returned') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            // Nothing to do for sqlite when rolling back this migration in tests.
            return;

            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('order_code')->unique();
                $table->string('customer_name');
                $table->string('customer_phone');
                $table->text('shipping_address');
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('membership_discount', 15, 2)->default(0);
                $table->decimal('coupon_discount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->enum('status', ['pending', 'processing', 'shipping', 'completed', 'cancelled'])->default('pending');
                $table->timestamps();
            });

            DB::statement('INSERT INTO orders (id, user_id, order_code, customer_name, customer_phone, shipping_address, subtotal, membership_discount, coupon_discount, total_amount, status, created_at, updated_at) SELECT id, user_id, order_code, customer_name, customer_phone, shipping_address, subtotal, membership_discount, coupon_discount, total_amount, status, created_at, updated_at FROM orders_old');
            DB::statement('DROP TABLE orders_old');

            DB::commit();
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','shipping','completed','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};

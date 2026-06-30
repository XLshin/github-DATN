<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                if (! Schema::hasColumn('shipments', 'carrier_id')) {
                    $table->foreignId('carrier_id')
                        ->nullable()
                        ->constrained('carriers')
                        ->nullOnDelete();
                }

                if (! Schema::hasColumn('shipments', 'shipment_code')) {
                    $table->string('shipment_code')
                        ->nullable()
                        ->index();
                }

                if (! Schema::hasColumn('shipments', 'status')) {
                    $table->enum('status', [
                        'pending',
                        'processing',
                        'waiting_handover',
                        'label_created',
                        'picked_up',
                        'in_transit',
                        'shipping',
                        'delivered',
                        'failed',
                        'returned',
                        'cancelled',
                    ])->default('pending');
                }

                if (! Schema::hasColumn('shipments', 'cost')) {
                    $table->decimal('cost', 15, 2)->nullable();
                }

                if (! Schema::hasColumn('shipments', 'service_type')) {
                    $table->string('service_type')->nullable();
                }

                if (! Schema::hasColumn('shipments', 'tracking_url')) {
                    $table->string('tracking_url')->nullable();
                }

                if (! Schema::hasColumn('shipments', 'requested_at')) {
                    $table->timestamp('requested_at')->nullable();
                }

                if (! Schema::hasColumn('shipments', 'picked_up_at')) {
                    $table->timestamp('picked_up_at')->nullable();
                }

                if (! Schema::hasColumn('shipments', 'metadata')) {
                    $table->json('metadata')->nullable();
                }
            });
        } else {
            Schema::create('shipments', function (Blueprint $table) {
                $table->id();

                $table->foreignId('order_id')
                    ->constrained('orders')
                    ->cascadeOnDelete();

                $table->foreignId('carrier_id')
                    ->nullable()
                    ->constrained('carriers')
                    ->nullOnDelete();

                $table->string('shipment_code')
                    ->nullable()
                    ->index();

                $table->enum('status', [
                    'pending',
                    'processing',
                    'waiting_handover',
                    'label_created',
                    'picked_up',
                    'in_transit',
                    'shipping',
                    'delivered',
                    'failed',
                    'returned',
                    'cancelled',
                ])->default('pending');

                $table->decimal('cost', 15, 2)->nullable();

                $table->string('service_type')->nullable();

                $table->string('tracking_url')->nullable();

                $table->json('metadata')->nullable();

                $table->string('shipping_unit')->nullable();

                $table->string('tracking_code')->nullable();

                $table->enum('shipping_status', [
                    'pending',
                    'processing',
                    'waiting_handover',
                    'shipping',
                    'delivered',
                    'failed',
                    'cancelled',
                ])->default('pending');

                $table->timestamp('requested_at')->nullable();
                $table->timestamp('picked_up_at')->nullable();
                $table->timestamp('shipped_at')->nullable();
                $table->timestamp('delivered_at')->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
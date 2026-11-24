<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('address_id')->nullable()->after('user_id')->constrained('addresses')->nullOnDelete();
            $table->json('shipping_address')->nullable()->after('address_id');
            $table->decimal('subtotal', 12, 2)->default(0)->after('shipping_address');
            $table->decimal('shipping_total', 12, 2)->default(0)->after('subtotal');
            $table->decimal('discount_total', 12, 2)->default(0)->after('shipping_total');
            $table->string('idempotency_key', 64)->nullable()->after('order_number');
            $table->unique(['user_id', 'idempotency_key']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('name', 255);
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
            $table->unique(['order_id', 'product_id']);
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('type', 32);
            $table->unsignedInteger('quantity');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('order_items');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'idempotency_key']);
            $table->dropColumn(['idempotency_key', 'discount_total', 'shipping_total', 'subtotal', 'shipping_address']);
            $table->dropConstrainedForeignId('address_id');
        });
    }
};

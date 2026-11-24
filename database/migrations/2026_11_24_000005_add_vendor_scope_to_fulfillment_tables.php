<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('product_id')->constrained('vendors')->nullOnDelete();
            $table->index(['order_id', 'vendor_id']);
        });

        DB::table('order_items')
            ->whereNotNull('product_id')
            ->update([
                'vendor_id' => DB::raw('(SELECT vendor_id FROM products WHERE products.id = order_items.product_id)'),
            ]);

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('order_id')->constrained('vendors')->nullOnDelete();
            $table->index(['order_id', 'vendor_id']);
        });

        Schema::table('shippings', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('order_id')->constrained('vendors')->nullOnDelete();
            $table->index(['order_id', 'vendor_id', 'status']);
        });

        $this->backfillSingleVendorFulfillment('payments');
        $this->backfillSingleVendorFulfillment('shippings');
    }

    public function down(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'vendor_id', 'status']);
            $table->dropConstrainedForeignId('vendor_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'vendor_id']);
            $table->dropConstrainedForeignId('vendor_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'vendor_id']);
            $table->dropConstrainedForeignId('vendor_id');
        });
    }

    private function backfillSingleVendorFulfillment(string $table): void
    {
        DB::table('orders')->orderBy('id')->chunkById(100, function ($orders) use ($table): void {
            foreach ($orders as $order) {
                $vendorIds = DB::table('order_items')
                    ->where('order_id', $order->id)
                    ->whereNotNull('vendor_id')
                    ->distinct()
                    ->pluck('vendor_id');

                if ($vendorIds->count() !== 1 || $vendorIds->contains(fn ($vendorId) => $vendorId === null)) {
                    continue;
                }

                DB::table($table)
                    ->where('order_id', $order->id)
                    ->whereNull('vendor_id')
                    ->update(['vendor_id' => $vendorIds->first()]);
            }
        });
    }
};

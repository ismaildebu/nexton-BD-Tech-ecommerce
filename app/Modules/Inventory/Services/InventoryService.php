<?php

namespace App\Modules\Inventory\Services;

use App\Models\User;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Product\Models\Product;
use DomainException;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function update(int $productId, int $quantity, ?int $threshold = null): Inventory
    {
        return Inventory::updateOrCreate(
            ['product_id' => $productId],
            array_filter(['quantity' => $quantity, 'low_stock_threshold' => $threshold], fn ($v) => $v !== null)
        );
    }

    public function adjust(int $productId, int $quantityChange, ?User $actor = null, ?string $note = null, ?int $threshold = null): Inventory
    {
        if ($quantityChange === 0) {
            throw new DomainException(__('Inventory adjustment cannot be zero.'));
        }

        if ($threshold !== null && $threshold < 0) {
            throw new DomainException(__('Low-stock threshold cannot be negative.'));
        }

        return DB::transaction(function () use ($productId, $quantityChange, $actor, $note, $threshold) {
            Product::whereKey($productId)->lockForUpdate()->firstOrFail();
            $inventory = Inventory::where('product_id', $productId)->lockForUpdate()->first();
            $newQuantity = (int) ($inventory?->quantity ?? 0) + $quantityChange;

            if ($newQuantity < 0) {
                throw new DomainException(__('Inventory cannot be reduced below zero.'));
            }

            if ($inventory === null) {
                $inventory = Inventory::create([
                    'product_id' => $productId,
                    'quantity' => $newQuantity,
                    'low_stock_threshold' => $threshold ?? 5,
                ]);
            } else {
                $inventory->update(array_filter([
                    'quantity' => $newQuantity,
                    'low_stock_threshold' => $threshold,
                ], fn ($value) => $value !== null));
            }

            InventoryMovement::create([
                'product_id' => $productId,
                'user_id' => $actor?->getKey(),
                'type' => $quantityChange > 0 ? 'adjustment_in' : 'adjustment_out',
                'quantity' => abs($quantityChange),
                'note' => $note,
                'created_at' => now(),
            ]);

            return $inventory->refresh();
        });
    }
}

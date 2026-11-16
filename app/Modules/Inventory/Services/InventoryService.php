<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\Inventory;

class InventoryService
{
    public function update(int $productId, int $quantity, ?int $threshold = null): Inventory
    {
        return Inventory::updateOrCreate(
            ['product_id' => $productId],
            array_filter(['quantity' => $quantity, 'low_stock_threshold' => $threshold], fn ($v) => $v !== null)
        );
    }
}

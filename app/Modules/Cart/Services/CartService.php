<?php

namespace App\Modules\Cart\Services;

use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Product\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function add(int $userId, int $productId, int $quantity): CartItem
    {
        return DB::transaction(function () use ($userId, $productId, $quantity) {
            User::whereKey($userId)->lockForUpdate()->firstOrFail();

            $product = Product::whereKey($productId)->lockForUpdate()->firstOrFail();
            $this->ensureAvailable($product);

            $cart = Cart::firstOrCreate(['user_id' => $userId]);
            $item = $cart->items()->lockForUpdate()->firstOrNew(['product_id' => $productId]);
            $newQuantity = ($item->quantity ?? 0) + $quantity;

            $this->ensureStock($product, $newQuantity);
            $item->quantity = $newQuantity;
            $item->save();

            return $item;
        });
    }

    public function update(CartItem $item, int $quantity): CartItem
    {
        return DB::transaction(function () use ($item, $quantity) {
            User::whereKey($item->cart->user_id)->lockForUpdate()->firstOrFail();

            $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
            $this->ensureAvailable($product);
            $this->ensureStock($product, $quantity);

            $item->update(['quantity' => $quantity]);

            return $item->refresh();
        });
    }

    public function remove(CartItem $item): void
    {
        DB::transaction(function () use ($item) {
            User::whereKey($item->cart->user_id)->lockForUpdate()->firstOrFail();

            CartItem::whereKey($item->getKey())->lockForUpdate()->firstOrFail()->delete();
        });
    }

    public function subtotal(Cart $cart): float
    {
        $subtotal = 0.0;

        foreach ($cart->items()->with('product')->get() as $item) {
            if ($item->product !== null) {
                $subtotal = round($subtotal + round((float) $item->product->price * $item->quantity, 2), 2);
            }
        }

        return $subtotal;
    }

    private function ensureAvailable(Product $product): void
    {
        if (! $product->isPurchasable()) {
            throw ValidationException::withMessages([
                'product_id' => __('This product is no longer available.'),
            ]);
        }
    }

    private function ensureStock(Product $product, int $quantity): void
    {
        $stock = Inventory::where('product_id', $product->id)->lockForUpdate()->value('quantity');

        if ($quantity > 999 || $stock === null || $stock < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => __('The requested quantity exceeds the allowed limit or available stock.'),
            ]);
        }
    }
}

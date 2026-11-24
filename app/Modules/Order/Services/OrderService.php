<?php

namespace App\Modules\Order\Services;

use App\Models\Address;
use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Coupon\Services\CouponService;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Product\Models\Product;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly CouponService $coupons,
        private readonly PaymentService $payments,
    ) {}

    public function create(int $userId, int $addressId, ?string $idempotencyKey = null, ?string $couponCode = null): Order
    {
        return DB::transaction(function () use ($userId, $addressId, $idempotencyKey, $couponCode) {
            User::whereKey($userId)->lockForUpdate()->firstOrFail();

            if ($idempotencyKey !== null) {
                $existing = Order::where('user_id', $userId)
                    ->where('idempotency_key', $idempotencyKey)
                    ->lockForUpdate()
                    ->first();

                if ($existing !== null) {
                    return $existing;
                }
            }

            $address = Address::where('user_id', $userId)->findOrFail($addressId);
            $cart = Cart::where('user_id', $userId)->lockForUpdate()->first();

            if ($cart === null) {
                throw new DomainException(__('Cart is empty.'));
            }

            $items = $cart->items()->lockForUpdate()->get();

            if ($items->isEmpty()) {
                throw new DomainException(__('Cart is empty.'));
            }

            $lines = [];
            $subtotal = 0.0;

            foreach ($items as $item) {
                if ($item->quantity < 1) {
                    throw new DomainException(__('Cart quantity is invalid.'));
                }

                $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();

                if (! $product->isPurchasable()) {
                    throw new DomainException(__('One or more products are no longer available.'));
                }

                $inventory = Inventory::where('product_id', $product->id)->lockForUpdate()->first();

                if ($inventory === null || $inventory->quantity < $item->quantity) {
                    throw new DomainException(__('Insufficient stock for :product.', ['product' => $product->name]));
                }

                $lineTotal = round((float) $product->price * $item->quantity, 2);
                $subtotal = round($subtotal + $lineTotal, 2);

                $lines[] = [
                    'product_id' => $product->id,
                    'vendor_id' => $product->vendor_id,
                    'inventory_id' => $inventory->id,
                    'name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $item->quantity,
                    'line_total' => $lineTotal,
                ];
            }

            $discount = 0.0;

            if ($couponCode !== null && trim($couponCode) !== '') {
                $coupon = $this->coupons->findApplicable($couponCode, $subtotal, lockForUpdate: true);
                $discount = $this->coupons->discount($coupon, $subtotal);
            }

            $total = round($subtotal - $discount, 2);

            $order = Order::create([
                'user_id' => $userId,
                'address_id' => $address->id,
                'shipping_address' => Arr::only($address->getAttributes(), [
                    'type', 'full_name', 'phone', 'address_line', 'division', 'district', 'upazila', 'postal_code',
                ]),
                'order_number' => 'ORD-'.Str::upper(Str::random(8)),
                'subtotal' => $subtotal,
                'shipping_total' => 0,
                'discount_total' => $discount,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'idempotency_key' => $idempotencyKey,
            ]);

            foreach ($lines as $line) {
                $order->items()->create(Arr::except($line, ['inventory_id']));

                Inventory::whereKey($line['inventory_id'])->decrement('quantity', $line['quantity']);

                InventoryMovement::create([
                    'product_id' => $line['product_id'],
                    'order_id' => $order->id,
                    'type' => 'order_placed',
                    'quantity' => $line['quantity'],
                    'created_at' => now(),
                ]);
            }

            $this->payments->recordForOrder($order, 'cod');

            $cart->items()->delete();

            return $order;
        });
    }

    public function transition(Order $order, string $status): Order
    {
        return match ($status) {
            'processing' => $this->startProcessing($order),
            'cancelled' => $this->cancel($order),
            default => throw new DomainException(__('Unsupported order status transition.')),
        };
    }

    public function startProcessing(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                throw new DomainException(__('Only pending orders can be moved to processing.'));
            }

            $locked->update(['status' => 'processing']);

            return $locked;
        });
    }

    public function cancel(Order $order, bool $byCustomer = false): Order
    {
        return DB::transaction(function () use ($order, $byCustomer) {
            $locked = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();

            if ($byCustomer) {
                if ($locked->status !== 'pending' || $locked->payment_status !== 'unpaid') {
                    throw new DomainException(__('Only pending orders with unpaid payment can be cancelled.'));
                }
            } elseif (in_array($locked->status, ['cancelled', 'completed'], true)) {
                throw new DomainException(__('Cancelled or completed orders cannot be cancelled.'));
            } elseif (! in_array($locked->status, ['pending', 'processing'], true)) {
                throw new DomainException(__('This order can no longer be cancelled.'));
            }

            $items = $order->items()->lockForUpdate()->orderBy('product_id')->get();
            $payments = $order->payments()->lockForUpdate()->orderBy('id')->get();
            $productIds = $items->pluck('product_id')->filter()->values();
            $inventories = Inventory::whereIn('product_id', $productIds)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            foreach ($items as $item) {
                $inventory = $productIds->isNotEmpty() ? $inventories->get($item->product_id) : null;

                if ($inventory === null) {
                    throw new DomainException(__('Inventory is unavailable for :product; the order was not cancelled.', [
                        'product' => $item->name,
                    ]));
                }

                $inventory->increment('quantity', $item->quantity);

                InventoryMovement::create([
                    'product_id' => $item->product_id,
                    'order_id' => $locked->id,
                    'type' => 'order_cancelled',
                    'quantity' => $item->quantity,
                    'created_at' => now(),
                ]);
            }

            $paidPayments = $payments->where('status', 'paid');

            foreach ($paidPayments as $payment) {
                $payment->update(['status' => 'refunded']);
            }

            $locked->update(['status' => 'cancelled']);
            $this->payments->synchronizeOrderPaymentStatus($locked);

            return $locked;
        });
    }
}

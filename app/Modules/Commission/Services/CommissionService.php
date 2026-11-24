<?php

namespace App\Modules\Commission\Services;

use App\Models\User;
use App\Modules\Commission\Models\Commission;
use App\Modules\Order\Models\Order;
use App\Modules\Vendor\Models\Vendor;
use DomainException;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public function record(int $vendorId, ?float $amount = null, ?float $rate = null, ?int $orderId = null, ?User $actor = null): Commission
    {
        if ($actor !== null && ! $actor->is_admin) {
            throw new DomainException(__('Only administrators or system processes can record commissions.'));
        }

        if ($amount !== null && (! is_finite($amount) || $amount <= 0 || $amount > 9999999999.99)) {
            throw new DomainException(__('The commission amount must be a positive amount within the supported range.'));
        }

        if ($rate !== null && (! is_finite($rate) || $rate < 0 || $rate > 100)) {
            throw new DomainException(__('The commission rate must be between zero and one hundred.'));
        }

        return DB::transaction(function () use ($vendorId, $amount, $rate, $orderId) {
            Vendor::whereKey($vendorId)->lockForUpdate()->firstOrFail();
            $rateWasProvided = $rate !== null;
            $rate ??= 0.0;

            if ($orderId !== null) {
                $order = Order::with('items.product')->lockForUpdate()->findOrFail($orderId);
                $base = (float) $order->items
                    ->filter(fn ($item) => $item->product?->vendor_id === $vendorId)
                    ->sum('line_total');

                if ($base <= 0) {
                    throw new DomainException(__('The selected order has no items for this vendor.'));
                }

                if ($rateWasProvided) {
                    $derived = round($base * ($rate / 100), 2);

                    if ($amount !== null && abs($amount - $derived) > 0.009) {
                        throw new DomainException(__('The commission amount does not match the order total and rate.'));
                    }

                    $amount = $derived;
                }
            }

            if ($amount === null) {
                throw new DomainException(__('A commission amount or order-linked rate is required.'));
            }

            $amount = round($amount, 2);

            if ($amount <= 0) {
                throw new DomainException(__('A commission amount or order-linked rate is required.'));
            }

            if ($orderId !== null) {
                $existingAmount = (float) Commission::where('vendor_id', $vendorId)
                    ->where('order_id', $orderId)
                    ->sum('amount');

                if ($existingAmount + $amount > $base + 0.009) {
                    throw new DomainException(__('An order-linked commission cannot exceed the vendor order total.'));
                }
            }

            return Commission::create([
                'vendor_id' => $vendorId,
                'order_id' => $orderId,
                'amount' => $amount,
                'rate' => round($rate, 2),
            ]);
        });
    }
}

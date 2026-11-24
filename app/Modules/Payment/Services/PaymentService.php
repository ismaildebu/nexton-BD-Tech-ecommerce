<?php

namespace App\Modules\Payment\Services;

use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\Payment;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    private const GATEWAYS = ['cod', 'bkash', 'nagad', 'rocket', 'card'];

    public function record(Order $order, ?string $gateway = null, ?int $vendorId = null): Payment
    {
        $gateway = $gateway ?? 'cod';

        if (! in_array($gateway, self::GATEWAYS, true)) {
            throw new DomainException(__('The selected payment gateway is invalid.'));
        }

        return DB::transaction(function () use ($order, $gateway, $vendorId) {
            $locked = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status === 'cancelled') {
                throw new DomainException(__('A payment cannot be recorded for a cancelled order.'));
            }

            $amounts = $this->vendorAmounts($locked);
            $vendorId = $this->resolveVendorId($amounts, $vendorId);
            $existing = Payment::where('order_id', $locked->id)
                ->where('vendor_id', $vendorId)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $payment = Payment::create([
                'order_id' => $locked->id,
                'vendor_id' => $vendorId,
                'amount' => $this->formatCents($amounts[$vendorId]),
                'gateway' => $gateway,
                'status' => 'pending',
            ]);
            $this->synchronizeOrderPaymentStatus($locked);

            return $payment;
        });
    }

    public function recordForOrder(Order $order, string $gateway = 'cod'): Collection
    {
        if (! in_array($gateway, self::GATEWAYS, true)) {
            throw new DomainException(__('The selected payment gateway is invalid.'));
        }

        return DB::transaction(function () use ($order, $gateway) {
            $locked = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $existing = Payment::where('order_id', $locked->id)->lockForUpdate()->get();

            if ($existing->isNotEmpty()) {
                return $existing;
            }

            $amounts = $this->vendorAmounts($locked);
            $payments = new Collection;

            foreach ($amounts as $vendorId => $amount) {
                $payments->push(Payment::create([
                    'order_id' => $locked->id,
                    'vendor_id' => $vendorId,
                    'amount' => $this->formatCents($amount),
                    'gateway' => $gateway,
                    'status' => 'pending',
                ]));
            }

            $this->synchronizeOrderPaymentStatus($locked);

            return $payments;
        });
    }

    public function markPaid(Payment $payment, ?string $transactionId = null): Payment
    {
        return DB::transaction(function () use ($payment, $transactionId) {
            $order = Order::whereKey($payment->order_id)->lockForUpdate()->firstOrFail();
            $locked = Payment::whereKey($payment->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                throw new DomainException(__('Only pending payments can be marked as paid.'));
            }

            if ($order->status === 'cancelled') {
                throw new DomainException(__('A cancelled order cannot be marked as paid.'));
            }

            $attributes = ['status' => 'paid'];

            if ($transactionId !== null && trim($transactionId) !== '') {
                $attributes['transaction_id'] = trim($transactionId);
            }

            $locked->update($attributes);
            $this->synchronizeOrderPaymentStatus($order);

            return $locked;
        });
    }

    public function markRefunded(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment) {
            $order = Order::whereKey($payment->order_id)->lockForUpdate()->firstOrFail();
            $locked = Payment::whereKey($payment->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'paid') {
                throw new DomainException(__('Only paid payments can be marked as refunded.'));
            }

            $locked->update(['status' => 'refunded']);
            $this->synchronizeOrderPaymentStatus($order);

            return $locked;
        });
    }

    public function payableAmount(Order $order, int $vendorId): string
    {
        $amounts = $this->vendorAmounts($order);

        if (! array_key_exists($vendorId, $amounts)) {
            throw new DomainException(__('The selected vendor has no items in this order.'));
        }

        return $this->formatCents($amounts[$vendorId]);
    }

    public function synchronizeOrderPaymentStatus(Order $order): void
    {
        $payments = Payment::where('order_id', $order->id)->lockForUpdate()->get();
        $order->update(['payment_status' => $this->statusForPayments($order->total, $payments)]);
    }

    public function statusForPayments(mixed $total, Collection $payments): string
    {
        $total = $this->cents($total);
        $paid = $this->cents($payments->where('status', 'paid')->sum('amount'));
        $refunded = $this->cents($payments->where('status', 'refunded')->sum('amount'));
        $pending = $this->cents($payments->where('status', 'pending')->sum('amount'));

        return match (true) {
            $paid >= $total && $payments->isNotEmpty() => 'paid',
            $paid > 0 => $refunded > 0 ? 'partially_refunded' : 'partially_paid',
            $refunded > 0 && $pending === 0 => 'refunded',
            $refunded > 0 => 'partially_refunded',
            default => 'unpaid',
        };
    }

    private function vendorAmounts(Order $order): array
    {
        $order->loadMissing('items');
        $totals = [];

        foreach ($order->items as $item) {
            if ($item->vendor_id === null) {
                throw new DomainException(__('The order contains an item without a vendor.'));
            }

            $totals[$item->vendor_id] = ($totals[$item->vendor_id] ?? 0) + $this->cents($item->line_total);
        }

        if ($totals === []) {
            throw new DomainException(__('The order has no vendor items.'));
        }

        ksort($totals);
        $subtotal = array_sum($totals);
        $total = $this->cents($order->total);
        $vendorIds = array_keys($totals);

        if ($subtotal === 0) {
            $share = intdiv($total, count($vendorIds));
            $allocated = 0;
            $amounts = [];

            foreach ($vendorIds as $index => $vendorId) {
                $amounts[$vendorId] = $index === count($vendorIds) - 1 ? $total - $allocated : $share;
                $allocated += $amounts[$vendorId];
            }

            return $amounts;
        }

        $runningBase = 0;
        $allocated = 0;
        $amounts = [];

        foreach ($vendorIds as $index => $vendorId) {
            $runningBase += $totals[$vendorId];
            $target = $index === count($vendorIds) - 1
                ? $total
                : (int) round($total * $runningBase / $subtotal);
            $amounts[$vendorId] = $target - $allocated;
            $allocated = $target;
        }

        return $amounts;
    }

    private function resolveVendorId(array $amounts, ?int $vendorId): int
    {
        if ($vendorId === null && count($amounts) === 1) {
            return array_key_first($amounts);
        }

        if ($vendorId === null || ! array_key_exists($vendorId, $amounts)) {
            throw new DomainException(__('A vendor with items in this order is required.'));
        }

        return $vendorId;
    }

    private function cents(mixed $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function formatCents(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }
}

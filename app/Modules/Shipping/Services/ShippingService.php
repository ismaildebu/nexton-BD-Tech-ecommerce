<?php

namespace App\Modules\Shipping\Services;

use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\Payment;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Shipping\Models\Shipping;
use DomainException;
use Illuminate\Support\Facades\DB;

class ShippingService
{
    public function __construct(private readonly PaymentService $payments) {}

    public function create(int $orderId, ?string $carrier = null, ?string $tracking = null, ?int $vendorId = null): Shipping
    {
        return DB::transaction(function () use ($orderId, $carrier, $tracking, $vendorId) {
            $order = Order::whereKey($orderId)->lockForUpdate()->firstOrFail();

            if ($order->status === 'cancelled') {
                throw new DomainException(__('A shipment cannot be created for a cancelled order.'));
            }

            $vendorIds = $order->items()->whereNotNull('vendor_id')->distinct()->pluck('vendor_id');

            if ($vendorId === null && $vendorIds->count() === 1) {
                $vendorId = (int) $vendorIds->first();
            } elseif ($vendorId === null && $vendorIds->count() > 1) {
                throw new DomainException(__('Select the vendor whose items are being shipped.'));
            } elseif ($vendorId !== null && ! $vendorIds->contains($vendorId)) {
                throw new DomainException(__('The selected vendor has no items in this order.'));
            }

            $activeShipment = Shipping::where('order_id', $order->id)
                ->where('vendor_id', $vendorId)
                ->whereIn('status', ['pending', 'shipped'])
                ->lockForUpdate()
                ->exists();

            if ($activeShipment) {
                throw new DomainException(__('This vendor order already has an active shipment.'));
            }

            return Shipping::create([
                'order_id' => $order->id,
                'vendor_id' => $vendorId,
                'carrier' => $carrier,
                'tracking_number' => $tracking,
                'status' => 'pending',
            ]);
        });
    }

    public function transition(Shipping $shipping, string $status): Shipping
    {
        return DB::transaction(function () use ($shipping, $status) {
            $order = Order::whereKey($shipping->order_id)->lockForUpdate()->firstOrFail();
            $locked = Shipping::whereKey($shipping->getKey())->lockForUpdate()->firstOrFail();

            if ($status === 'shipped') {
                if ($locked->status !== 'pending') {
                    throw new DomainException(__('Only pending shipments can be marked as shipped.'));
                }

                if (in_array($order->status, ['cancelled', 'completed'], true)) {
                    throw new DomainException(__('Cancelled or completed orders cannot be shipped.'));
                }

                $locked->update(['status' => 'shipped']);
                $order->update(['status' => 'shipped']);

                return $locked;
            }

            if ($status === 'delivered') {
                if ($locked->status !== 'shipped') {
                    throw new DomainException(__('Only shipped shipments can be marked as delivered.'));
                }

                if ($order->status === 'cancelled') {
                    throw new DomainException(__('A cancelled order cannot be delivered.'));
                }

                $payments = Payment::where('order_id', $order->id)
                    ->where('vendor_id', $locked->vendor_id)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $target = $locked->vendor_id === null
                    ? $this->cents($order->total)
                    : $this->cents($this->payments->payableAmount($order, $locked->vendor_id));
                $remaining = max(0, $target - $this->cents($payments->where('status', 'paid')->sum('amount')));

                foreach ($payments->where('status', 'pending')->where('gateway', 'cod') as $payment) {
                    $amount = $this->cents($payment->amount);

                    if ($amount > $remaining) {
                        continue;
                    }

                    $payment->update(['status' => 'paid']);
                    $remaining -= $amount;
                }

                $this->payments->synchronizeOrderPaymentStatus($order);
                $locked->update(['status' => 'delivered']);
                $this->synchronizeFulfillmentStatus($order);

                return $locked;
            }

            throw new DomainException(__('Unsupported shipping status transition.'));
        });
    }

    private function cents(mixed $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function synchronizeFulfillmentStatus(Order $order): void
    {
        $requiredVendorIds = $order->items()->whereNotNull('vendor_id')->distinct()->pluck('vendor_id');
        $deliveredVendorIds = $order->shippings()
            ->where('status', 'delivered')
            ->whereNotNull('vendor_id')
            ->distinct()
            ->pluck('vendor_id');

        if ($requiredVendorIds->isEmpty() || $requiredVendorIds->diff($deliveredVendorIds)->isEmpty()) {
            $order->update(['status' => 'completed']);

            return;
        }

        if (! in_array($order->status, ['cancelled', 'completed'], true)) {
            $order->update(['status' => 'shipped']);
        }
    }
}

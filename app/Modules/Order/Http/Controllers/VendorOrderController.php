<?php

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Shipping\Models\Shipping;
use App\Modules\Shipping\Services\ShippingService;
use App\Modules\Vendor\Models\Vendor;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VendorOrderController extends Controller
{
    public function __construct(
        private readonly ShippingService $shipping,
        private readonly PaymentService $payments,
    ) {}

    public function index(Request $request): View
    {
        $vendor = Vendor::accessibleTo($request->user())->firstOrFail();
        $orders = $this->ordersFor($vendor)
            ->with(['items.product', 'payments', 'shippings'])
            ->latest()
            ->paginate(20);

        $orders->getCollection()->transform(fn (Order $order) => $this->scopeRelations($order, $vendor));

        return view('vendor.orders.index', ['vendor' => $vendor, 'orders' => $orders]);
    }

    public function show(Request $request, Order $order): View
    {
        $vendor = Vendor::accessibleTo($request->user())->firstOrFail();
        $order = $this->ordersFor($vendor)->with(['items.product', 'payments', 'shippings'])->findOrFail($order->getKey());
        $this->scopeRelations($order, $vendor);

        return view('vendor.orders.show', ['vendor' => $vendor, 'order' => $order]);
    }

    public function createShipment(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'carrier' => ['required', 'string', 'max:128'],
            'tracking_number' => ['required', 'string', 'max:128'],
        ]);

        $vendor = Vendor::accessibleTo($request->user())->firstOrFail();
        $order = $this->ordersFor($vendor)->findOrFail($order->getKey());

        try {
            $this->shipping->create($order->id, $data['carrier'], $data['tracking_number'], $vendor->id);
        } catch (DomainException $exception) {
            return redirect()->route('vendor.orders.show', $order)->with('error', $exception->getMessage());
        }

        return redirect()->route('vendor.orders.show', $order)->with('status', __('Shipment created.'));
    }

    public function ship(Request $request, Order $order, Shipping $shipping): RedirectResponse
    {
        $vendor = Vendor::accessibleTo($request->user())->firstOrFail();
        $order = $this->ordersFor($vendor)->findOrFail($order->getKey());
        $shipping = $this->vendorShipment($order, $vendor, $shipping);

        try {
            $this->shipping->transition($shipping, 'shipped');
        } catch (DomainException $exception) {
            return redirect()->route('vendor.orders.show', $order)->with('error', $exception->getMessage());
        }

        return redirect()->route('vendor.orders.show', $order)->with('status', __('Order marked as shipped.'));
    }

    public function deliver(Request $request, Order $order, Shipping $shipping): RedirectResponse
    {
        $vendor = Vendor::accessibleTo($request->user())->firstOrFail();
        $order = $this->ordersFor($vendor)->findOrFail($order->getKey());
        $shipping = $this->vendorShipment($order, $vendor, $shipping);

        try {
            $this->shipping->transition($shipping, 'delivered');
        } catch (DomainException $exception) {
            return redirect()->route('vendor.orders.show', $order)->with('error', $exception->getMessage());
        }

        return redirect()->route('vendor.orders.show', $order)->with('status', __('Vendor fulfillment marked as delivered.'));
    }

    private function vendorShipment(Order $order, Vendor $vendor, Shipping $shipping): Shipping
    {
        return Shipping::whereKey($shipping->getKey())
            ->where('order_id', $order->id)
            ->where('vendor_id', $vendor->id)
            ->firstOrFail();
    }

    private function scopeRelations(Order $order, Vendor $vendor): Order
    {
        $order->setRelation('items', $order->items
            ->filter(fn ($item) => (int) $item->vendor_id === $vendor->id)
            ->values());
        $order->setRelation('payments', $order->payments
            ->filter(fn ($payment) => (int) $payment->vendor_id === $vendor->id)
            ->values());
        $order->setRelation('shippings', $order->shippings
            ->filter(fn ($shipment) => (int) $shipment->vendor_id === $vendor->id)
            ->values());
        $order->setAttribute('vendor_payment_status', $this->payments->statusForPayments(
            $order->payments->sum('amount'),
            $order->payments,
        ));

        return $order;
    }

    private function ordersFor(Vendor $vendor): Builder
    {
        return Order::whereHas('items', fn (Builder $query) => $query->where('vendor_id', $vendor->id));
    }
}

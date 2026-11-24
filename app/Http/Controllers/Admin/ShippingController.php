<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Order\Models\Order;
use App\Modules\Shipping\Http\Requests\CreateAdminShippingRequest;
use App\Modules\Shipping\Http\Requests\TransitionShippingRequest;
use App\Modules\Shipping\Models\Shipping;
use App\Modules\Shipping\Services\ShippingService;
use App\Modules\Vendor\Models\Vendor;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShippingController extends Controller
{
    public function __construct(private readonly ShippingService $shipping) {}

    public function index(): View
    {
        $shippings = Shipping::with(['order:id,order_number,status', 'vendor:id,business_name'])->latest()->paginate(20);
        $orders = Order::where('status', '!=', 'cancelled')->latest()->limit(100)->get(['id', 'order_number', 'status']);
        $vendors = Vendor::orderBy('business_name')->limit(100)->get(['id', 'business_name']);

        return view('admin.shipping.index', compact('shippings', 'orders', 'vendors'));
    }

    public function store(CreateAdminShippingRequest $request): RedirectResponse
    {
        try {
            $this->shipping->create(
                $request->integer('order_id'),
                $request->input('carrier'),
                $request->input('tracking_number'),
                $request->filled('vendor_id') ? $request->integer('vendor_id') : null,
            );
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', __('Shipment created.'));
    }

    public function transition(TransitionShippingRequest $request, Shipping $shipping): RedirectResponse
    {
        try {
            $this->shipping->transition($shipping, $request->string('status')->toString());
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', __('Shipment status updated.'));
    }
}

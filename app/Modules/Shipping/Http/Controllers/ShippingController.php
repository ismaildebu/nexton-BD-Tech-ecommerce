<?php

namespace App\Modules\Shipping\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shipping\Http\Requests\CreateShippingRequest;
use App\Modules\Shipping\Models\Shipping;
use App\Modules\Shipping\Services\ShippingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(private readonly ShippingService $shipping) {}

    public function index(Request $request): View
    {
        $shippings = Shipping::whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id))->latest()->paginate(20);

        return view('shipping.index', ['shippings' => $shippings]);
    }

    public function store(CreateShippingRequest $request): RedirectResponse
    {
        $this->shipping->create(
            $request->integer('order_id'),
            $request->input('carrier'),
            $request->input('tracking_number')
        );

        return redirect()->route('shipping.index')->with('status', __('Shipment created.'));
    }
}

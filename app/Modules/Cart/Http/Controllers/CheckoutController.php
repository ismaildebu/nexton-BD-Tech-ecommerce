<?php

namespace App\Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Http\Requests\CheckoutRequest;
use App\Modules\Coupon\Services\CouponService;
use App\Modules\Order\Services\OrderService;
use DomainException;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function store(CheckoutRequest $request): RedirectResponse
    {
        try {
            $order = $this->orders->create(
                $request->user()->id,
                $request->integer('address_id'),
                $request->string('idempotency_key')->toString(),
                $request->session()->get(CouponService::SESSION_KEY)
            );
        } catch (DomainException $exception) {
            return redirect()->route('cart.index')->with('error', $exception->getMessage());
        }

        if ($order->wasRecentlyCreated) {
            $request->session()->forget(CouponService::SESSION_KEY);
        }

        return redirect()->route('orders.index')->with('status', __('Order placed.'));
    }
}

<?php

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Coupon\Services\CouponService;
use App\Modules\Order\Http\Requests\CreateOrderRequest;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Services\OrderService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function index(Request $request): View
    {
        $orders = Order::where('user_id', $request->user()->id)->latest()->paginate(20);

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Request $request, int $order): View
    {
        $order = Order::where('user_id', $request->user()->id)
            ->with(['items', 'payments', 'shippings'])
            ->findOrFail($order);

        return view('orders.show', ['order' => $order]);
    }

    public function cancel(Request $request, int $order): RedirectResponse
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($order);

        try {
            $this->orders->cancel($order, byCustomer: true);
        } catch (DomainException $exception) {
            return redirect()->route('orders.show', $order)->with('error', $exception->getMessage());
        }

        return redirect()->route('orders.show', $order)->with('status', __('Order cancelled.'));
    }

    public function store(CreateOrderRequest $request): RedirectResponse
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

        return redirect()->route('orders.index')->with('status', __('Order created.'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Order\Http\Requests\TransitionOrderRequest;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Services\OrderService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function index(): View
    {
        return view('admin.orders.index', ['orders' => Order::latest()->paginate(20)]);
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'payments', 'shippings', 'user:id,name,email']);

        return view('admin.orders.show', ['order' => $order]);
    }

    public function transition(TransitionOrderRequest $request, Order $order): RedirectResponse
    {
        try {
            $this->orders->transition($order, $request->string('status')->toString());
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', __('Order status updated.'));
    }
}

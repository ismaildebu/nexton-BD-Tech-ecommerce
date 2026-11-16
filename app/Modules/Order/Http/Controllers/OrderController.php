<?php

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Http\Requests\CreateOrderRequest;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Services\OrderService;
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

    public function store(CreateOrderRequest $request): RedirectResponse
    {
        $this->orders->create($request->user()->id);

        return redirect()->route('orders.index')->with('status', __('Order created.'));
    }
}

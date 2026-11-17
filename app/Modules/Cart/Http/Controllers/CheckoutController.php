<?php

namespace App\Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Models\Cart;
use App\Modules\Order\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function store(Request $request): RedirectResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)->with('items.product')->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('status', __('Cart is empty.'));
        }

        $this->orders->create($request->user()->id);

        return redirect()->route('orders.index')->with('status', __('Order placed.'));
    }
}

<?php

namespace App\Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Http\Requests\AddToCartRequest;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(Request $request): View
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        return view('cart.index', ['cart' => $cart->load('items.product')]);
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $this->cart->add(
            $request->user()->id,
            $request->integer('product_id'),
            $request->integer('quantity')
        );

        return redirect()->route('cart.index')->with('status', __('Added to cart.'));
    }
}

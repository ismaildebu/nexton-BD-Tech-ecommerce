<?php

namespace App\Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Http\Requests\AddToCartRequest;
use App\Modules\Cart\Http\Requests\UpdateCartItemRequest;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Cart\Services\CartService;
use App\Modules\Coupon\Services\CouponService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CouponService $coupons,
    ) {}

    public function index(Request $request): View
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $subtotal = $this->cart->subtotal($cart);
        $coupon = null;
        $discount = 0.0;
        $couponError = null;
        $couponCode = $request->session()->get(CouponService::SESSION_KEY);

        if (is_string($couponCode) && trim($couponCode) !== '') {
            try {
                $coupon = $this->coupons->findApplicable($couponCode, $subtotal);
                $discount = $this->coupons->discount($coupon, $subtotal);
            } catch (DomainException $exception) {
                $request->session()->forget(CouponService::SESSION_KEY);
                $couponError = $exception->getMessage();
            }
        }

        return view('cart.index', [
            'cart' => $cart->load('items.product.inventory'),
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->get(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => round($subtotal - $discount, 2),
            'coupon' => $coupon,
            'couponError' => $couponError,
        ]);
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        try {
            $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
            $coupon = $this->coupons->findApplicable($validated['code'], $this->cart->subtotal($cart));
        } catch (DomainException $exception) {
            return redirect()->route('cart.index')->withInput()->with('error', $exception->getMessage());
        }

        $request->session()->put(CouponService::SESSION_KEY, $coupon->code);

        return redirect()->route('cart.index')->with('status', __('Coupon :code applied.', ['code' => $coupon->code]));
    }

    public function removeCoupon(Request $request): RedirectResponse
    {
        $request->session()->forget(CouponService::SESSION_KEY);

        return redirect()->route('cart.index')->with('status', __('Coupon removed.'));
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

    public function update(UpdateCartItemRequest $request, CartItem $item): RedirectResponse
    {
        Gate::authorize('update', $item->cart);

        $this->cart->update($item, $request->integer('quantity'));

        return redirect()->route('cart.index')->with('status', __('Cart updated.'));
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        Gate::authorize('update', $item->cart);

        $this->cart->remove($item);

        return redirect()->route('cart.index')->with('status', __('Item removed.'));
    }
}

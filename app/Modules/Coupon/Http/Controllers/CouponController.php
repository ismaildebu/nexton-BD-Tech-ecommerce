<?php

namespace App\Modules\Coupon\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Coupon\Http\Requests\CreateCouponRequest;
use App\Modules\Coupon\Models\Coupon;
use App\Modules\Coupon\Services\CouponService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CouponController extends Controller
{
    public function __construct(private readonly CouponService $coupons) {}

    public function index(): View
    {
        return view('coupons.index', ['coupons' => Coupon::latest()->paginate(20)]);
    }

    public function store(CreateCouponRequest $request): RedirectResponse
    {
        $this->coupons->create($request->validated() + ['is_active' => true]);

        return redirect()->route('admin.coupons.index')->with('status', __('Coupon created.'));
    }
}

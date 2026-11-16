<?php

use App\Modules\Coupon\Http\Controllers\CouponController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/coupons', [CouponController::class, 'index'])->name('admin.coupons.index');
    Route::post('/admin/coupons', [CouponController::class, 'store'])->name('admin.coupons.store');
});

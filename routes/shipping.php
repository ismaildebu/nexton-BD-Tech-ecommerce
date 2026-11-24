<?php

use App\Modules\Shipping\Http\Controllers\ShippingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping.index');
    Route::post('/shipping', [ShippingController::class, 'store'])->middleware('admin')->name('shipping.store');
});

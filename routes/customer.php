<?php

use App\Modules\Customer\Http\Controllers\CustomerAccountController;
use App\Modules\Customer\Http\Controllers\CustomerDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/customer/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');
    Route::get('/customer/account', [CustomerAccountController::class, 'index'])->name('customer.account.index');
});

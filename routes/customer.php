<?php

use App\Modules\Customer\Http\Controllers\CustomerAccountController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/customer/account', [CustomerAccountController::class, 'index'])->name('customer.account.index');
});

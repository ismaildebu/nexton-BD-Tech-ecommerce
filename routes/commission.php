<?php

use App\Modules\Commission\Http\Controllers\CommissionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');
    Route::post('/commissions', [CommissionController::class, 'store'])->name('commissions.store');
});

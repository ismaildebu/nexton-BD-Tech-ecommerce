<?php

use App\Http\Controllers\HomeController;
use App\Modules\Customer\Http\Controllers\CustomerDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::middleware('auth')->get('/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');

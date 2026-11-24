<?php

use App\Modules\Identity\Http\Controllers\AddressController;
use App\Modules\Identity\Http\Controllers\AuthenticatedSessionController;
use App\Modules\Identity\Http\Controllers\PasswordResetController;
use App\Modules\Identity\Http\Controllers\ProfileController;
use App\Modules\Identity\Http\Controllers\RegisteredUserController;
use App\Modules\Identity\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Identity & Authentication routes (Module 01)
|--------------------------------------------------------------------------
| Loaded from bootstrap/app.php inside the "web" middleware group,
| so sessions and CSRF protection apply to every route here.
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('register.store');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('/reset-password-phone', [PasswordResetController::class, 'createPhone'])->name('password.phone');
    Route::post('/reset-password-phone', [PasswordResetController::class, 'updatePhone'])
        ->middleware('throttle:6,1')
        ->name('password.phone.update');
});

Route::get('/verify-email/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');
Route::get('/verification', [VerificationController::class, 'notice'])->name('verification.notice');
Route::get('/verification/phone', [VerificationController::class, 'phone'])->name('verification.phone');
Route::post('/verification/phone', [VerificationController::class, 'verifyPhone'])
    ->middleware('throttle:10,1')
    ->name('verification.phone.store');
Route::post('/verification/resend', [VerificationController::class, 'resend'])
    ->middleware('throttle:6,1')
    ->name('verification.resend');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::resource('addresses', AddressController::class)->except('show');
    Route::patch('/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.default');
});

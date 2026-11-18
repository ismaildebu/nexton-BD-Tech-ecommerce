<?php

use App\Modules\Vendor\Http\Controllers\VendorBankAccountController;
use App\Modules\Vendor\Http\Controllers\VendorDocumentController;
use App\Modules\Vendor\Http\Controllers\VendorRegistrationController;
use App\Modules\Vendor\Http\Controllers\VendorStaffController;
use App\Modules\Vendor\Models\Vendor;

Route::middleware('auth')->group(function () {
        Route::resource('vendor/products', \App\Modules\Product\Http\Controllers\VendorProductController::class)
        ->except(['show', 'destroy'])
        ->names('vendor.products')
        ->parameters(['products' => 'product']);
    Route::get('/vendor/dashboard', [\App\Modules\Vendor\Http\Controllers\VendorDashboardController::class, 'index'])->name('vendor.dashboard');
    Route::get('/vendor/register', [VendorRegistrationController::class, 'create'])->name('vendor.register.create');
    Route::post('/vendor/register', [VendorRegistrationController::class, 'store'])->name('vendor.register.store');
    Route::get('/vendor/registration/status', [VendorRegistrationController::class, 'status'])->name('vendor.registration.status');
    Route::get('/vendor/{vendor}/documents', [VendorDocumentController::class, 'index'])->name('vendor.documents.index');
    Route::post('/vendor/{vendor}/documents', [VendorDocumentController::class, 'store'])->name('vendor.documents.store');
    Route::get('/vendor/{vendor}/staff', [VendorStaffController::class, 'index'])->name('vendor.staff.index');
    Route::post('/vendor/{vendor}/staff', [VendorStaffController::class, 'store'])->name('vendor.staff.store');
});

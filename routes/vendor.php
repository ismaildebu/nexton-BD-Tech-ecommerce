<?php

use App\Modules\Inventory\Http\Controllers\VendorInventoryController;
use App\Modules\Order\Http\Controllers\VendorOrderController;
use App\Modules\Product\Http\Controllers\VendorProductController;
use App\Modules\Vendor\Http\Controllers\AdminVendorVerificationController;
use App\Modules\Vendor\Http\Controllers\VendorBankAccountController;
use App\Modules\Vendor\Http\Controllers\VendorDocumentController;
use App\Modules\Vendor\Http\Controllers\VendorProfileController;
use App\Modules\Vendor\Http\Controllers\VendorRegistrationController;
use App\Modules\Vendor\Http\Controllers\VendorStaffController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/vendor/dashboard', [\App\Modules\Vendor\Http\Controllers\VendorDashboardController::class, 'index'])->name('vendor.dashboard');

    Route::get('/vendor/register', [VendorRegistrationController::class, 'create'])->name('vendor.register.create');
    Route::post('/vendor/register', [VendorRegistrationController::class, 'store'])->name('vendor.register.store');
    Route::get('/vendor/registration/status', [VendorRegistrationController::class, 'status'])->name('vendor.registration.status');
    Route::post('/vendor/registration/resubmit', [VendorRegistrationController::class, 'resubmit'])->name('vendor.registration.resubmit');

    Route::get('/vendor/invitations/{token}', [VendorStaffController::class, 'showInvitation'])->name('vendor.staff.invitation');
    Route::post('/vendor/invitations/{token}/accept', [VendorStaffController::class, 'accept'])->name('vendor.staff.invitation.accept');
    Route::post('/vendor/invitations/{token}/decline', [VendorStaffController::class, 'decline'])->name('vendor.staff.invitation.decline');

    Route::resource('vendor/products', VendorProductController::class)
        ->except(['show'])
        ->names('vendor.products')
        ->parameters(['products' => 'product']);

    Route::get('/vendor/inventory', [VendorInventoryController::class, 'index'])->name('vendor.inventory.index');
    Route::post('/vendor/inventory/adjust', [VendorInventoryController::class, 'adjust'])->name('vendor.inventory.adjust');

    Route::get('/vendor/orders', [VendorOrderController::class, 'index'])->name('vendor.orders.index');
    Route::get('/vendor/orders/{order}', [VendorOrderController::class, 'show'])->name('vendor.orders.show');
    Route::post('/vendor/orders/{order}/shipping', [VendorOrderController::class, 'createShipment'])->name('vendor.orders.shipping.store');
    Route::post('/vendor/orders/{order}/shipping/{shipping}/ship', [VendorOrderController::class, 'ship'])->name('vendor.orders.shipping.ship');
    Route::post('/vendor/orders/{order}/shipping/{shipping}/deliver', [VendorOrderController::class, 'deliver'])->name('vendor.orders.shipping.deliver');

    Route::get('/vendor/{vendor}/settings', [VendorProfileController::class, 'edit'])->name('vendor.settings.edit');
    Route::put('/vendor/{vendor}/settings', [VendorProfileController::class, 'update'])->name('vendor.settings.update');
    Route::get('/vendor/{vendor}/bank-accounts', [VendorBankAccountController::class, 'index'])->name('vendor.bank-accounts.index');
    Route::post('/vendor/{vendor}/bank-accounts', [VendorBankAccountController::class, 'store'])->name('vendor.bank-accounts.store');
    Route::get('/vendor/{vendor}/documents', [VendorDocumentController::class, 'index'])->name('vendor.documents.index');
    Route::post('/vendor/{vendor}/documents', [VendorDocumentController::class, 'store'])->name('vendor.documents.store');
    Route::get('/vendor/{vendor}/staff', [VendorStaffController::class, 'index'])->name('vendor.staff.index');
    Route::post('/vendor/{vendor}/staff', [VendorStaffController::class, 'store'])->name('vendor.staff.store');
    Route::post('/vendor/{vendor}/staff/{vendorUser}/revoke', [VendorStaffController::class, 'revoke'])->name('vendor.staff.revoke');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/admin/vendors/{vendor}/suspend', [AdminVendorVerificationController::class, 'suspend'])->name('admin.vendors.suspend');
    Route::get('/admin/vendors/{vendor}/documents/{document}/preview', [AdminVendorVerificationController::class, 'previewDocument'])->name('admin.vendors.documents.preview');
});

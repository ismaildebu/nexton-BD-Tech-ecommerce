<?php

use App\Modules\Vendor\Http\Controllers\AdminVendorVerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('admin.customers.index');
    Route::get('/admin/products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('admin.products.index');
    Route::post('/admin/products/{product}/approve', [\App\Http\Controllers\Admin\ProductController::class, 'approve'])->name('admin.products.approve');
    Route::post('/admin/products/{product}/reject', [\App\Http\Controllers\Admin\ProductController::class, 'reject'])->name('admin.products.reject');

    Route::get('/admin/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/admin/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('admin.payments.index');
    Route::get('/admin/shipping', [\App\Http\Controllers\Admin\ShippingController::class, 'index'])->name('admin.shipping.index');
    Route::get('/admin/commissions', [\App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('admin.commissions.index');
    Route::get('/admin/settlements', [\App\Http\Controllers\Admin\SettlementController::class, 'index'])->name('admin.settlements.index');
    Route::get('/admin/subscriptions', [\App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('admin.subscriptions.index');
    Route::get('/admin/reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('admin.reviews.index');
    Route::get('/admin/cms', [\App\Http\Controllers\Admin\CmsController::class, 'index'])->name('admin.cms.index');
    Route::get('/admin/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('admin.notifications.index');
    Route::get('/admin/wishlist', [\App\Http\Controllers\Admin\WishlistController::class, 'index'])->name('admin.wishlist.index');
    Route::prefix('admin/vendors')->name('admin.vendors.')->group(function () {
    Route::get('/', [AdminVendorVerificationController::class, 'index'])->name('index');
    Route::get('/{vendor}', [AdminVendorVerificationController::class, 'show'])->name('show');
    Route::post('/{vendor}/approve', [AdminVendorVerificationController::class, 'approve'])->name('approve');
    Route::post('/{vendor}/reject', [AdminVendorVerificationController::class, 'reject'])->name('reject');
});
});

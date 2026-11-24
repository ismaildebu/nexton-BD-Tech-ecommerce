<?php

use App\Modules\Vendor\Http\Controllers\AdminVendorVerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', fn () => redirect()->route('admin.dashboard'))->name('admin.home');
    Route::get('/admin/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('admin.customers.index');
    Route::get('/admin/products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('admin.products.index');
    Route::post('/admin/products/{product}/approve', [\App\Http\Controllers\Admin\ProductController::class, 'approve'])->name('admin.products.approve');
    Route::post('/admin/products/{product}/reject', [\App\Http\Controllers\Admin\ProductController::class, 'reject'])->name('admin.products.reject');
    Route::resource('admin/categories', \App\Http\Controllers\Admin\CategoryController::class)
        ->only(['index', 'create', 'store', 'edit', 'update'])
        ->names('admin.categories');
    Route::patch('/admin/categories/{category}/toggle', [\App\Http\Controllers\Admin\CategoryController::class, 'toggle'])->name('admin.categories.toggle');
    Route::resource('admin/brands', \App\Http\Controllers\Admin\BrandController::class)
        ->only(['index', 'create', 'store', 'edit', 'update'])
        ->names('admin.brands');
    Route::patch('/admin/brands/{brand}/toggle', [\App\Http\Controllers\Admin\BrandController::class, 'toggle'])->name('admin.brands.toggle');

    Route::get('/admin/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/admin/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('admin.orders.show');
    Route::post('/admin/orders/{order}/transition', [\App\Http\Controllers\Admin\OrderController::class, 'transition'])->name('admin.orders.transition');
    Route::get('/admin/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('admin.payments.index');
    Route::post('/admin/payments/{payment}/transition', [\App\Http\Controllers\Admin\PaymentController::class, 'transition'])->name('admin.payments.transition');
    Route::get('/admin/shipping', [\App\Http\Controllers\Admin\ShippingController::class, 'index'])->name('admin.shipping.index');
    Route::post('/admin/shipping', [\App\Http\Controllers\Admin\ShippingController::class, 'store'])->name('admin.shipping.store');
    Route::post('/admin/shipping/{shipping}/transition', [\App\Http\Controllers\Admin\ShippingController::class, 'transition'])->name('admin.shipping.transition');
    Route::get('/admin/commissions', [\App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('admin.commissions.index');
    Route::get('/admin/settlements', [\App\Http\Controllers\Admin\SettlementController::class, 'index'])->name('admin.settlements.index');
    Route::post('/admin/settlements', [\App\Http\Controllers\Admin\SettlementController::class, 'store'])->name('admin.settlements.store');
    Route::post('/admin/settlements/{settlement}/transition', [\App\Http\Controllers\Admin\SettlementController::class, 'transition'])->name('admin.settlements.transition');
    Route::get('/admin/subscriptions', [\App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('admin.subscriptions.index');
    Route::post('/admin/subscriptions', [\App\Http\Controllers\Admin\SubscriptionController::class, 'store'])->name('admin.subscriptions.store');
    Route::post('/admin/subscriptions/{subscription}/transition', [\App\Http\Controllers\Admin\SubscriptionController::class, 'transition'])->name('admin.subscriptions.transition');
    Route::get('/admin/reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('admin.reviews.index');
    Route::delete('/admin/reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('admin.reviews.destroy');
    Route::get('/admin/cms', [\App\Http\Controllers\Admin\CmsController::class, 'index'])->name('admin.cms.index');
    Route::get('/admin/cms/create', [\App\Http\Controllers\Admin\CmsController::class, 'create'])->name('admin.cms.create');
    Route::get('/admin/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('admin.notifications.index');
    Route::get('/admin/notifications/create', [\App\Http\Controllers\Admin\NotificationController::class, 'create'])->name('admin.notifications.create');
    Route::post('/admin/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'store'])->name('admin.notifications.store');
    Route::get('/admin/wishlist', [\App\Http\Controllers\Admin\WishlistController::class, 'index'])->name('admin.wishlist.index');
    Route::prefix('admin/vendors')->name('admin.vendors.')->group(function () {
    Route::get('/', [AdminVendorVerificationController::class, 'index'])->name('index');
    Route::get('/{vendor}', [AdminVendorVerificationController::class, 'show'])->name('show');
    Route::post('/{vendor}/approve', [AdminVendorVerificationController::class, 'approve'])->name('approve');
    Route::post('/{vendor}/reject', [AdminVendorVerificationController::class, 'reject'])->name('reject');
});
});

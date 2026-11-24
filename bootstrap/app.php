<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Module 01: Identity & Authentication
            Route::middleware('web')->group(base_path('routes/identity.php'));
            // Module 02: Customer Management
            Route::middleware('web')->group(base_path('routes/customer.php'));
            // Module 03: Vendor Management
            Route::middleware('web')->group(base_path('routes/vendor.php'));
            Route::middleware('web')->group(base_path('routes/admin-vendor.php'));
            // Module 04: Product Catalog
            Route::middleware('web')->group(base_path('routes/product.php'));
            // Inventory
            Route::middleware('web')->group(base_path('routes/inventory.php'));
            // Cart
            Route::middleware('web')->group(base_path('routes/cart.php'));
            // Checkout
            Route::middleware('web')->group(base_path('routes/checkout.php'));
            // Order
            Route::middleware('web')->group(base_path('routes/order.php'));
            // Payment
            Route::middleware('web')->group(base_path('routes/payment.php'));
            // Shipping
            Route::middleware('web')->group(base_path('routes/shipping.php'));
            // Commission
            Route::middleware('web')->group(base_path('routes/commission.php'));
            // Settlement
            Route::middleware('web')->group(base_path('routes/settlement.php'));
            // Subscription
            Route::middleware('web')->group(base_path('routes/subscription.php'));
            // Review
            Route::middleware('web')->group(base_path('routes/review.php'));
            // CMS
            Route::middleware('web')->group(base_path('routes/cms.php'));
            // Notification
            Route::middleware('web')->group(base_path('routes/notification.php'));
            // Wishlist
            Route::middleware('web')->group(base_path('routes/wishlist.php'));
            // Coupon
            Route::middleware('web')->group(base_path('routes/coupon.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('profile.edit'));
        $middleware->appendToGroup('web', \App\Modules\Identity\Http\Middleware\EnsureAccountIsActive::class);
        $middleware->alias(['admin' => \App\Modules\Vendor\Http\Middleware\EnsureAdmin::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

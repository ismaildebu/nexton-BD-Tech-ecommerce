<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Identity\Providers\IdentityServiceProvider::class,
    App\Modules\Customer\Providers\CustomerServiceProvider::class,
    App\Modules\Vendor\Providers\VendorServiceProvider::class,
    App\Modules\Product\Providers\ProductServiceProvider::class,
    App\Modules\Inventory\Providers\InventoryServiceProvider::class,
    App\Modules\Cart\Providers\CartServiceProvider::class,
    App\Modules\Order\Providers\OrderServiceProvider::class,
    App\Modules\Payment\Providers\PaymentServiceProvider::class,
    App\Modules\Shipping\Providers\ShippingServiceProvider::class,
    App\Modules\Commission\Providers\CommissionServiceProvider::class,
    App\Modules\Accounting\Providers\AccountingServiceProvider::class,
    App\Modules\Subscription\Providers\SubscriptionServiceProvider::class,
    App\Modules\Review\Providers\ReviewServiceProvider::class,
    App\Modules\Cms\Providers\CmsServiceProvider::class,
    App\Modules\Notification\Providers\NotificationServiceProvider::class,
    App\Modules\Wishlist\Providers\WishlistServiceProvider::class,
    App\Modules\Coupon\Providers\CouponServiceProvider::class,
];

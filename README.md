# Nexton BD Tech E-Commerce

Laravel 12 modular monolith e-commerce platform with admin panel and frontend layout.

## Modules

- Identity (auth, roles)
- Customer
- Vendor (registration, approval, documents, bank accounts, staff)
- Product (catalog, categories, attributes)
- Inventory (stock management)
- Cart
- Order (checkout, order service)
- Payment
- Shipping
- Commission
- Accounting / Settlement
- Subscription
- Review
- CMS (pages, banners)
- Notification
- Wishlist
- Coupon

## Stack

- Laravel 12, PHP 8.2+
- Blade, Tailwind CSS, Alpine.js
- MySQL/SQLite
- Chart.js (admin dashboard)

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=DemoDataSeeder
npm run build
php artisan serve
```

## Admin

- URL: `/admin`
- Admin users: `mallick.jess@gmail.com`, `admin@example.com` (is_admin = true)
- Sidebar navigation covers all modules (products, orders, payments, shipping, commissions, settlements, subscriptions, reviews, cms, notifications, wishlist, coupons, customers, vendors).

## Frontend

- Layout: `resources/views/layouts/frontend.blade.php`
- Color palette: #253442 (navy), #6852D9 (purple), #F4F6F9 (background)

## Routes

All module routes registered in `bootstrap/app.php` via `Route::middleware('web')->group(...)`.

## Demo Data

```bash
php artisan db:seed --class=DemoDataSeeder
```

Seeds admin, vendor, customer, category, 10 products with inventory, 10 orders with payments, reviews, coupon `SAVE10`, notification.

## Testing

```bash
php artisan test
php artisan test --filter=Vendor
```

## License

MIT

# Nexton BD Tech E-Commerce

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue)
![License](https://img.shields.io/badge/License-MIT-green)

Laravel 12 modular monolith e-commerce platform with admin panel and frontend layout.

## Architecture

- Modular monolith under `app/Modules/` (Identity, Customer, Vendor, Product, Inventory, Cart, Order, Payment, Shipping, Commission, Accounting, Subscription, Review, Cms, Notification, Wishlist, Coupon).
- Each module contains controllers, requests, services, policies, models, routes.
- Admin controllers under `app/Http/Controllers/Admin/`.
- Routes registered in `bootstrap/app.php`.

## Directory Structure

```
app/
  Http/Controllers/Admin/
  Modules/
bootstrap/app.php
database/seeders/DemoDataSeeder.php
resources/views/
  layouts/admin.blade.php
  layouts/frontend.blade.php
  admin/
routes/
```

## Modules

- Identity (auth, roles)
- Customer (profile, addresses)
- Vendor (registration, approval, documents, bank accounts, staff)
- Product (catalog, categories, attributes, variants)
- Inventory (stock management, adjustments)
- Cart (add/update/remove, totals)
- Order (checkout, order service, status flow)
- Payment (gateways, transactions)
- Shipping (methods, tracking)
- Commission (vendor commissions)
- Accounting / Settlement (payouts)
- Subscription (plans, billing)
- Review (product reviews, moderation)
- CMS (pages, banners)
- Notification (email/SMS/in-app)
- Wishlist
- Coupon (discounts)

## Stack

- Laravel 12, PHP 8.2+
- Blade, Tailwind CSS, Alpine.js
- MySQL/SQLite
- Chart.js (admin dashboard)

## Environment

Copy `.env.example` to `.env` and set DB credentials. Default SQLite supported.

Key variables:
- `APP_NAME`, `APP_URL`
- `DB_CONNECTION`, `DB_DATABASE`
- `MAIL_*` for notifications

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
- Demo admin: `admin@example.com` (password: `password`, is_admin = true)
- Demo vendor: `vendor@example.com` (password: `password`)
- Demo customer: `customer@example.com` (password: `password`)
- Sidebar navigation covers all modules (products, orders, payments, shipping, commissions, settlements, subscriptions, reviews, cms, notifications, wishlist, coupons, customers, vendors).

## Frontend

- Layout: `resources/views/layouts/frontend.blade.php`
- Color palette: #253442 (navy), #6852D9 (purple), #F4F6F9 (background)

## Routes

All module routes registered in `bootstrap/app.php` via `Route::middleware('web')->group(...)`.

Admin routes under `/admin/*` (products, orders, payments, shipping, commissions, settlements, subscriptions, reviews, cms, notifications, wishlist, coupons, customers, vendors).

## Demo Data

```bash
php artisan db:seed --class=DemoDataSeeder
```

Seeds admin, vendor, customer, category, 10 products with inventory, 10 orders with payments, reviews, coupon `SAVE10`, notification.

## Testing

```bash
php artisan test
php artisan test --filter=Vendor
php artisan test --filter=Product
```

## Deployment

- Set `APP_ENV=production`, `APP_DEBUG=false`.
- Run `php artisan config:cache route:cache view:cache`.
- Serve `public/` via web server; enable HTTPS.

## Security

- Admin middleware protects `/admin`.
- CSRF enabled by default.
- Validate all requests via Form Requests.

## Contributing

1. Create feature branch.
2. Follow PSR-12 and existing module patterns.
3. Run tests before PR.

## Changelog

- 2026-11-16: Scaffolded all modules, admin UI, demo data, README.

## Troubleshooting

- Run `php artisan optimize:clear` after config changes.
- Run `npm run build` after frontend changes.
- Ensure `storage/` and `bootstrap/cache/` writable.

## License

MIT

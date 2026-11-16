# Nexton BD Tech E-Commerce

Laravel 12 modular monolith e-commerce platform (Identity, Customer, Vendor, Product, Inventory, Cart, Order, Payment, Shipping, Commission, Accounting, Subscription, Review, CMS, Notification, Wishlist, Coupon).

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=DemoDataSeeder
npm run build
```

## Admin

- `/admin` dashboard
- Admin users: `mallick.jess@gmail.com`, `admin@example.com`

## Tests

```bash
php artisan test
```

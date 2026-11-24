# Nexton BD Tech E-Commerce

[![Laravel](https://img.shields.io/badge/Laravel-12-red)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue)](https://www.php.net)
![License](https://img.shields.io/badge/License-MIT-green)

A Laravel 12 modular-monolith e-commerce application with customer, vendor, and admin workflows; transactional checkout; configurable storefront appearance; and three homepage design presets.

The application includes the core commerce workflows, but payment gateways, carrier APIs, SMS delivery, and recurring billing are **manual or placeholder workflows**, not live third-party integrations.

## Features

### Storefront and customer account

- Responsive storefront with configurable branding, typography, spacing, radius, content width, header, footer, and card styles.
- Six configurable homepage sections: hero, categories, product spotlight, products, vendors, and benefits.
- Three homepage presets: **Signature**, **Bazaar**, and **Editorial**.
- Product search, category filtering, product details, cart, coupon application, and checkout.
- Customer dashboard with profile, addresses, orders, payments, shipments, wishlist, reviews, and notifications.
- Wishlist and product-review creation for authenticated customers.

### Vendor workspace

- Vendor registration, approval status, documents, bank accounts, and staff membership.
- Vendor-scoped dashboard and catalog management.
- Vendor-scoped inventory, order fulfillment, shipping, commission, settlement, and subscription views.
- Vendor policies restrict access to the merchant's own records and active staff memberships.

### Administration

- Admin dashboard and management workflows for customers, vendors, catalog, inventory, orders, payments, shipping, commissions, settlements, subscriptions, coupons, CMS page creation, and notifications.
- Site settings at `/admin/settings` for branding, announcement, contact/footer content, appearance tokens, and homepage management.
- Admin access is based on the user's `is_admin` flag and protected by the `admin` middleware alias.

### Commerce and data integrity

- Cart ownership and quantity checks.
- Checkout address ownership validation, server-side total recalculation, duplicate-submission protection, and transactional order creation.
- Row locking for payment and fulfillment transitions where concurrent updates matter.
- Order-line and address snapshots, per-vendor payment/shipment allocation, and inventory audit movements.
- Coupon eligibility, usage, expiry, minimum-order, and discount validation.
- Manual payment, shipping, commission, settlement, and subscription state transitions.

## Architecture

This is a **modular monolith**, not a package-based or microservice application.

- Seventeen modules live under `app/Modules/`.
- Each module owns its controllers, requests, services, models, policies, and provider as applicable.
- Shared admin controllers live under `app/Http/Controllers/Admin/`.
- Appearance and homepage behavior is centralized under `app/Support/`.
- HTTP route files live under `routes/` and are loaded centrally from `bootstrap/app.php`.
- Modules do not register their own route files.
- Site settings are stored in a singleton `site_settings` record.
- Homepage section data is JSON-backed and sanitized through an allowlist before it is saved or rendered.

### Registered modules

| Module | Responsibility |
| --- | --- |
| Identity | Authentication, password reset, verification, active-account enforcement |
| Customer | Profile, addresses, customer dashboard |
| Vendor | Registration, approval, documents, bank accounts, staff |
| Product | Products, categories, brands, approval state |
| Inventory | Stock, adjustments, inventory movements |
| Cart | Cart items and totals |
| Order | Checkout, orders, order lifecycle |
| Payment | Manual payment records and transitions |
| Shipping | Manual shipment records and tracking |
| Commission | Vendor commission records |
| Accounting | Settlements and payout workflows |
| Subscription | Manual subscription records and transitions |
| Review | Product review listing and creation |
| Cms | CMS page creation and public display |
| Notification | Database-backed notifications |
| Wishlist | Customer wishlists |
| Coupon | Coupon administration and checkout discounts |

### Main entry points

| Area | Path |
| --- | --- |
| Storefront | `/` |
| Authentication | `/login`, `/register` |
| Customer dashboard | `/dashboard` |
| Vendor dashboard | `/vendor/dashboard` |
| Vendor registration | `/vendor/register` |
| Admin dashboard | `/admin` |
| Site settings | `/admin/settings` |
| Public CMS page | `/pages/{slug}` |
| Health check | `/up` |

## Tech stack

- PHP 8.2 or newer
- Laravel 12
- Blade
- Tailwind CSS 4
- Alpine.js
- Vite 6
- MySQL or SQLite
- PHPUnit 11, Mockery, Pint, Pail, and Sail for development and testing

The project does not include Chart.js or another dashboard charting dependency.

## Requirements

- PHP 8.2 or newer with the extensions required by Laravel
- Composer
- Node.js and npm
- A supported SQLite or MySQL database

For SQLite, no separate database server is required.

## Local setup

From the project root:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate

# Create the default SQLite database file without overwriting an existing one.
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"

php artisan migrate
php artisan storage:link

# Optional: load demo accounts and catalog data into a development database.
php artisan db:seed --class=DemoDataSeeder

npm run build
php artisan serve
```

Open the local URL printed by Artisan. The default application URL is usually `http://127.0.0.1:8000`.

On Windows PowerShell, use `Copy-Item .env.example .env` instead of `cp`.

### MySQL configuration

To use MySQL, update `.env` before running migrations:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Create the database first, then run `php artisan migrate`.

### Environment defaults

`.env.example` defaults to SQLite. If `DB_DATABASE` is unset, Laravel uses `database/database.sqlite`.

Other development defaults are:

```dotenv
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
MAIL_MAILER=log
PHONE_VERIFICATION_DRIVER=log
```

The bundled phone-verification drivers are development-log and no-op implementations. The log driver is disabled in production, and no real SMS provider is included.

## Demo accounts

After running `DemoDataSeeder` on a fresh development database:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@example.com` | `password` |
| Vendor | `vendor@example.com` | `password` |
| Customer | `customer@example.com` | `password` |

The seeder creates an approved, active demo vendor, 10 products with inventory, 10 orders with items and payments, reviews, the `SAVE10` coupon, and a notification.

The seeder uses `firstOrCreate`; rerunning it does not reset existing account passwords or replace existing records. Never use these demo credentials in production.

## Site appearance and homepage management

Open `/admin/settings` as an admin.

### Appearance

Administrators can configure runtime design tokens and shared UI styles, including:

- Brand and semantic colors
- Typography
- Spacing and corner radius
- Content width
- Header, footer, and card styles
- Branding, announcement, and contact/footer content

### Homepage sections

Each of the six homepage sections supports visibility, ordering, custom headings/descriptions, and an allowlisted layout where applicable. The hero also supports a custom HTTP/HTTPS image URL.

Available layout families are:

| Section | Layout options |
| --- | --- |
| Hero | `split`, `centered`, `mosaic`, `campaign` |
| Categories | `cards`, `circles`, `rail`, `tiles` |
| Spotlight | `horizontal`, `editorial`, `bestsellers`, `story` |
| Products | `grid`, `editorial`, `shelf`, `compact` |
| Vendors | `grid`, `list`, `logos` |
| Benefits | `cards`, `strip`, `minimal` |

### Homepage presets

| Preset | Summary |
| --- | --- |
| **Signature** | Split hero, category cards, horizontal spotlight, product grid, vendor grid, benefit cards |
| **Bazaar** | Mosaic hero, category rail, bestseller spotlight, product shelf, vendor logos, benefit strip |
| **Editorial** | Campaign hero, category tiles, story spotlight, editorial product layout, vendor list, minimal benefits |

Changing presets applies the preset's section layouts while preserving custom copy, section visibility, ordering, and the hero image URL. An explicitly empty hero image remains gradient-only; an omitted value uses the default image.

The homepage's product spotlight and product section currently use the latest approved, active products from approved, active vendors. Labels such as “popular” or “bestseller” are presentation labels, not sales-volume rankings.

## Security

Implemented controls include:

- CSRF protection on web forms.
- Global active-account enforcement for web requests.
- Admin routes protected by the `admin` middleware alias and `is_admin` authorization.
- Vendor and product policies that enforce ownership and active staff access.
- Form Request validation plus inline validation in controllers and services.
- Uploaded-logo and vendor-document validation for file type, size, and dimensions where applicable.
- Transactional checkout, payment, shipment, coupon, and inventory operations.
- Address ownership and idempotency checks during checkout.
- Server-side price recalculation and order snapshots to avoid trusting client-supplied totals.

Uploaded files should still be protected with production-grade malware scanning and restrictive server/storage policies when handling untrusted vendor documents.

## Testing

Run the full feature suite:

```bash
php artisan test
```

Run focused areas:

```bash
php artisan test --filter=Vendor
php artisan test --filter=Product
php artisan test --filter=HomepageSectionTest
php artisan test --filter=MobileNavigationTest
```

Build frontend assets:

```bash
npm run build
```

`phpunit.xml` forces tests to use SQLite `:memory:` with array sessions, array mail, and the synchronous queue. Tests do not use the development database configured in `.env`.

Responsive storefront verification covers 320px, 393px, and 1440px viewport widths.

## Deployment

1. Install production dependencies and build assets:

   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   ```

2. Configure production environment values, including `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY`, `APP_URL`, database credentials, mail settings, and cache/session/queue drivers.
3. Back up the database before migrations. Some legacy migrations consolidate or remove duplicate records, so test the upgrade against staging first.
4. Run migrations and create the storage link:

   ```bash
   php artisan migrate --force
   php artisan storage:link
   ```

5. Cache configuration, routes, and views:

   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

6. Serve the application's `public/` directory through a production web server and enable HTTPS.
7. Keep `storage/` and `bootstrap/cache/` writable by the application process.
8. If queued jobs are enabled, run a supervised queue worker:

   ```bash
   php artisan queue:work
   ```

`public/build`, `public/hot`, `.env`, keys, and database files are ignored by Git. Build assets must therefore be generated during deployment. No Docker, CI, or hosting-specific deployment manifest is included in this repository.

## Known limitations

- Payment gateway names such as bKash, Nagad, Rocket, and card are ledger labels; live gateway callbacks and settlement integrations are not included.
- Shipping uses manual carrier, tracking, and status workflows; carrier APIs and shipping-rate calculation are not included.
- Subscriptions are manual records and transitions; recurring billing is not integrated.
- Phone verification has development-log and no-op drivers only; production SMS delivery requires a new provider integration.
- Products do not currently have image galleries, attributes, or variant records.
- CMS routes support public page display and admin page creation, but not edit/delete or banner management.
- Product reviews support listing and creation, but there is no moderation queue.
- Homepage benefits copy is currently static rather than editable per section.
- The default hero image and fonts use external network resources.
- There is no independent accessibility audit or formal security certification in this repository.

## Troubleshooting

### Homepage design or section controls are missing

Run pending migrations, clear cached configuration, and rebuild views:

```bash
php artisan migrate
php artisan optimize:clear
```

Do not run migrations against an existing production database without a tested backup and upgrade plan.

### Appearance changes are not visible

```bash
php artisan optimize:clear
npm run build
```

If serving a cached production site, rerun `config:cache` and `view:cache` after the change.

### Uploaded images or documents are not visible

```bash
php artisan storage:link
```

Also verify web-server access to `storage/app/public` and permissions on `storage/`.

### Login or form submissions fail after deployment

Confirm that `APP_KEY` is set and stable, sessions/cache are configured correctly, and the web server serves requests over the same `APP_URL`. Clear stale caches with `php artisan optimize:clear`.

### SMS verification does not work in production

This is expected with the bundled drivers. The log driver intentionally throws in production, and the no-op driver does not deliver messages. Integrate a real SMS provider before enabling production phone verification.

### Tests appear to use the wrong database

Confirm that `phpunit.xml` still forces `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`, then clear configuration cache:

```bash
php artisan optimize:clear
php artisan test
```

## Contributing

1. Create a feature branch.
2. Follow PSR-12 and the existing module/service/policy patterns.
3. Keep route registration centralized under `routes/`.
4. Add or update focused feature tests for changed workflows.
5. Run the full test suite and frontend build before opening a pull request.

## Changelog

- **2026-11-16** — Initial modular scaffold, admin/storefront UI, demo data, and documentation.
- **2026-11-24** — Strengthened authorization, checkout/payment/shipping integrity, merchant/customer workflows, inventory, CMS, coupon, and settings foundations.
- **2026-11-25** — Added configurable homepage sections, runtime appearance settings, three homepage presets, and responsive storefront navigation fixes.

## License

MIT, as declared in `composer.json`.

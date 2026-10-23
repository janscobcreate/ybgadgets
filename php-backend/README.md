# YB Gadgets — Optional PHP/MySQL backend

The hosted static client presentation has **browser-local** products, cart, order
requests, swaps and support messages. Its demo admin at `/admin/` is deliberately
not connected to a shared database and has no authentication. Do not use that
demo to collect real customer or payment data.

This directory provides a separate backend for a conventional PHP hosting plan.
It includes PHP 8.1+ / MySQL 8+ (or compatible MariaDB) source with:

- Prepared-statement product create/edit/delete and validated photo uploads
- Login protected with `password_hash()` / `password_verify()`, sessions and CSRF
- Order requests, swap requests, customer messages and status updates
- phpMyAdmin-compatible database import
- JSON API for an integrated frontend

## Configure

1. Get a PHP hosting plan with HTTPS, PDO MySQL and mbstring. GitHub Pages **does
   not execute PHP**.
2. Create a MySQL database and import `database.sql` using phpMyAdmin.
3. Copy `config.example.php` to `config.php`. Set database credentials.
4. Generate a password hash from a long unique password:

   `php -r "echo password_hash('REPLACE_WITH_YOUR_LONG_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"`

   Paste the resulting hash in `admin_password_hash`.
5. Place the backend on your HTTPS host, protect `config.php` from web access,
   and ensure `admin.php` is reachable. The provided `.htaccess` works on Apache.
6. Sign in to `admin.php`, add the real inventory, and use the PHP admin.

## Integration

The PHP API is at `api.php`. A future frontend deployment must replace its
browser-local persistence calls with this API. You can run both on the same
origin using an Apache rewrite from `/api/store` to `/php-backend/api.php`;
the current static demo does **not** make that request.

Public API:

- `GET api.php` — products from MySQL
- `POST api.php` with JSON `{"type":"order",...}`
- `POST api.php` with JSON `{"type":"swap",...}`
- `POST api.php` with JSON `{"type":"support",...}`

Admin JSON API requires an authenticated PHP session and a matching
`X-CSRF-Token` header. The included PHP admin uses server-rendered forms.

## Production checklist

Before accepting real orders: connect the public storefront to this API,
verify inventory and image ownership, set up payment/delivery processes,
add rate limiting/spam protection and email notifications, secure backups,
privacy policy and terms, and test the full workflow on your hosting.

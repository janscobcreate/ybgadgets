# YB Gadgets — GitHub showcase

This project contains two different deployment paths:

## Client presentation: GitHub Pages / static hosting

Upload the **contents of `dist/`** as the repository root:
`index.html`, `admin/index.html`, and `assets/`.

Enable GitHub Pages from the `main` branch, root folder. The storefront and
`admin/` dashboard will work as a fast static prototype. Product edits,
order requests, swaps and support messages are stored **only in the browser**
and are visible to that browser's admin dashboard; they do not reach a shared
server. The prototype admin is intentionally unauthenticated. Do not use for
real customers or payments.

There are 14 illustrative products; the images and prices are placeholders,
not verified stock or current offers.

## PHP + MySQL path: shared live operations

`php-backend/` contains database.sql, a login-protected PHP admin, JSON API,
and setup instructions. It must be deployed to PHP 8.1+ hosting with MySQL.
GitHub Pages cannot execute PHP; moving to the live system also requires
connecting the storefront to the PHP API.

For full deployment instructions, see `php-backend/README.md`.

# NinjaPortal Shadow Theme

`shadow-theme` is a Blade + Alpine.js + Tailwind CSS v4 + daisyUI frontend theme package for NinjaPortal.

It provides a production-ready developer portal UI that integrates directly with `ninjaportal/portal` services (and LaraApigee through the portal package), without relying on `portal-api`.

## What It Covers

- Landing page for a developer portal
- API product catalog browsing, searching, and detail pages
- Developer signup, sign in, sign out
- Password reset request + password reset completion
- Session-based auth flow implemented inside the theme package (not JWT / not `portal-api`)
- Developer profile management
- Developer app management (create/update/delete/approve/revoke)
- Developer credential management (create/approve/revoke/delete)
- Credential product management (add/remove/approve/revoke product access)
- Optional MFA (authenticator app + email OTP) when `ninjaportal/portal-mfa` is installed and enabled
- Reusable Blade components for building custom pages

## Requirements

- Laravel 11 or 12
- PHP `^8.2`
- `ninjaportal/portal` installed and configured
- Tailwind CSS v4 in your application Vite setup
- Node.js + npm/pnpm/yarn for frontend asset builds

## Installation

### 1. Install the package

```bash
composer require ninjaportal/shadow-theme
```

### 2. Ensure the core portal package is configured

Shadow Theme expects the NinjaPortal core to be available and configured:

- `ninjaportal/portal`
- `lordjoo/laraapigee` (used by portal for Apigee-backed app/credential operations)

### 3. Install frontend dependencies (daisyUI + Alpine)

```bash
npm install alpinejs daisyui
```

## Tailwind v4 + daisyUI Integration

Shadow Theme renders Blade views, so your application Tailwind build must scan the package views.

Update your Tailwind v4 entry CSS (example: `resources/css/app.css`):

```css
@import 'tailwindcss';
@plugin "daisyui";

/* Composer-installed package path */
@source '../../vendor/ninjaportal/shadow-theme/resources/views/**/*.blade.php';

/* Optional: local package path (useful in monorepos) */
@source '../../packages/shadow-theme/resources/views/**/*.blade.php';
```

Initialize Alpine in your application JS entry (example: `resources/js/app.js`):

```js
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
```

## Configuration

Publish the config file if you want to customize branding, routing, and features:

```bash
php artisan vendor:publish --tag=shadow-theme-config
```

Main config file:

- `config/shadow-theme.php`

### Common env keys

```dotenv
SHADOW_THEME_ENABLED=true
SHADOW_THEME_ROUTE_PREFIX=portal
SHADOW_THEME_BRAND_NAME="NinjaPortal"
SHADOW_THEME_BRAND_TAGLINE="A modern developer portal experience for your APIs."
SHADOW_THEME_LOGO_TEXT="Shadow"
SHADOW_THEME_SUPPORT_EMAIL=support@example.com

SHADOW_THEME_DEFAULT_MODE=dark
SHADOW_THEME_LIGHT_DAISY_THEME=corporate
SHADOW_THEME_DARK_DAISY_THEME=night

SHADOW_THEME_REGISTRATION_ENABLED=true
SHADOW_THEME_PASSWORD_RESET_ENABLED=true
SHADOW_THEME_MFA_UI_ENABLED=true
```

## Route Mounting

By default, the theme is mounted under:

- `/portal`

Examples:

- `/portal`
- `/portal/products`
- `/portal/login`
- `/portal/dashboard`
- `/portal/apps`

Set `SHADOW_THEME_ROUTE_PREFIX=` (empty value) to mount it at the root of your application.

## Auth Flow (Important)

Shadow Theme implements its own **session-based** auth flow for developer/consumer users.

This means:

- it does **not** depend on `portal-api` login endpoints
- it does **not** use `portal-api` JWT token issuance for the web theme
- it talks directly to `ninjaportal/portal` services and models

This is intentional so the Blade theme can be used independently from the REST API package.

## Optional MFA Support

If `ninjaportal/portal-mfa` is installed and enabled, Shadow Theme automatically adds:

- login MFA challenge page (consumer side)
- MFA settings page under the developer profile area
- authenticator app enrollment flow
- email OTP enrollment flow

Shadow Theme integrates with the MFA drivers/services directly and still keeps a session-based login flow for the web UI.

## Reusable Blade Components

The package registers an anonymous component namespace:

- `x-shadow::*`

Included components:

- `x-shadow::ui.flash`
- `x-shadow::ui.card`
- `x-shadow::ui.page-header`
- `x-shadow::ui.input`
- `x-shadow::ui.textarea`
- `x-shadow::ui.select`
- `x-shadow::ui.empty-state`
- `x-shadow::product.card`
- `x-shadow::app.status-badge`

These can be reused in your own application pages to maintain a consistent UI style.

## View Customization

You can customize the theme in two main ways:

1. Adjust branding/theme values in `config/shadow-theme.php`
2. Override/publish package views and customize the Blade templates

## Notes

- Shadow Theme uses `ninjaportal/portal` services directly (no `portal-api` runtime dependency for the theme flow).
- Authentication in Shadow Theme is session-based and separate from the JWT auth flow used in `portal-api`.
- App and credential operations are Apigee-backed through LaraApigee via the portal service layer.

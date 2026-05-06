# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 13 starter kit with Livewire, authentication, and role/permission management. Stack:
- **PHP** ^8.3
- **Laravel Fortify** for authentication (registration, login, password reset, 2FA)
- **Spatie Laravel Permission** for roles & permissions
- **Livewire v4.x** + **Livewire Flux v2.x** UI components (https://livewire.laravel.com/docs/4.x, https://fluxui.dev/docs)
- **Tailwind CSS v4.x**
- **Pest v4** for testing
- **Pint** for code formatting
- **SQLite** as the default database

## Commands

```bash
# Initial setup (install deps, generate key, migrate, install frontend)
composer setup

# Run dev server + queue worker + vite concurrently
composer dev

# Run lint:check + tests
composer test

# Lint check / fix
composer lint:check
composer lint

# Run a single test
./vendor/bin/pest --filter="test_name"

# Seed database (creates roles, permissions, menus, locations, demo users)
php artisan migrate:fresh --seed
```

## Architecture

### Authentication Flow
- `app/Actions/Fortify/CreateNewUser.php` — creates new users with profile/password validation
- `app/Actions/Fortify/ResetUserPassword.php` — password reset
- `app/Concerns/` — `PasswordValidationRules` and `ProfileValidationRules` traits, used by Fortify actions and Livewire settings components
- `app/Models/User.php` — extends Fortify's `TwoFactorAuthenticatable`, uses Spatie's `HasRoles` trait. Includes extended profile fields: `gender`, `phonenumber`, `religion`, `is_active`, `country_id`, `province_id`, `city_id`
- Auth views: `resources/views/pages/auth/`
- Settings pages: `resources/views/pages/settings/` (Livewire components)

### Authorization (Spatie Permission)
- Permissions and roles are defined in `database/seeders/RolesPermissionsSeeder.php`
- Permissions follow `<resource>.<action>` naming (e.g. `users.view`, `roles.edit`, `posts.delete`)
- Roles seeded: `superadmin` (granted everything via Gate::before — see `AuthServiceProvider` if present), `administrator`, `editor`, `staff`
- All permissions use `guard_name => 'web'`
- When adding new resources, add permissions to `RolesPermissionsSeeder` and assign to relevant roles

### Routing
- `routes/web.php` — main web routes (home, dashboard)
- `routes/settings.php` — settings sub-routes (profile, appearance, security)
- Settings routes use Livewire with the `pages::` namespace (e.g., `pages::settings.profile`)
- Fortify's home redirect is `/dashboard` (see `config/fortify.php`)

### Domain Models
- `User`, `Post` — main app entities
- `Country`, `Province`, `City` — location hierarchy used by user profile (seeded by `CountrySeeder`/`ProvinceSeeder`/`CitySeeder`)
- `Menu` — navigation entries seeded by `MenuSeeder`
- `Permission` — extends Spatie's permission model (custom logic if added)

### UI Components
- Blade components: `resources/views/components/`
- Layouts: `resources/views/layouts/` (`app.blade.php`, `auth.blade.php`)
- Flux components: `resources/views/flux/`

### Testing
- Pest framework (not bare PHPUnit). Feature tests in `tests/Feature/`, unit tests in `tests/Unit/`
- `RefreshDatabase` is available but commented out in `tests/Pest.php` — uncomment to enable per-test DB resets
- `composer test` runs `config:clear` + `lint:check` + `php artisan test`

### Key Configuration
- `config/fortify.php` — Fortify features, home path (`/dashboard`), 2FA settings
- `config/auth.php` — guards and password brokers
- `config/permission.php` — Spatie permission cache and table names
- `database/database.sqlite` — auto-created on setup

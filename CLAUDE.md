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
- `app/Concerns/` — `PasswordValidationRules`, `ProfileValidationRules`, `HasFileUpload`, `FileUploadValidationRules` traits
- `app/Models/User.php` — extends Fortify's `TwoFactorAuthenticatable`, uses Spatie's `HasRoles`, `SoftDeletes`, `InteractsWithMedia` (avatar uploads). Direct fields: `email`, `password`, `name`, `phonenumber`, `gender`, `is_active`, `company_id`, `branch_id`. Related to Profile via `hasOne()` (1-to-1)
- `app/Models/Profile.php` — 1-to-1 relationship with User via `belongsTo()`. Stores extended info: `identity_number`, `religion_id`, `birth_date`, `marital_status_id`, `address`, location (`country_id`, `province_id`, `city_id`). Uses SoftDeletes and audit fields (`created_by`, `updated_by`, `deleted_by`)
- Auth views: `resources/views/pages/auth/`
- Settings pages: `resources/views/pages/settings/` (Livewire components, view-only except avatar upload)

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
- `User` — main user entity with SoftDeletes, HasRoles, HasMedia (avatar), related to Profile via hasOne (1-to-1)
- `Profile` — extended user profile (1-to-1 with User) with SoftDeletes, stores: identity_number, religion_id, birth_date, marital_status_id, address, location (country/province/city)
- `Country`, `Province`, `City` — location hierarchy used by user/profile (seeded by `CountrySeeder`/`ProvinceSeeder`/`CitySeeder`)
- `Company`, `Branch` — organizational hierarchy, linked to User
- `Parameter` — flexible key-value store for system parameters (UUID key type), used for religion, marital_status, and other enums (seeded by `ParameterSeeder`)
- `Menu` — navigation entries seeded by `MenuSeeder`, only active pages are included
- `Permission` — extends Spatie's permission model

### Pages & Admin Features
Current implemented pages (navbar + sidebar menus):
- **Dashboard** — `routes/web.php` / `dashboard.blade.php`
- **Users** — CRUD with role assignment. Create/Edit pages: single-column card layout with sections (Account, Personal Info, Contact & Address, Identity & Legal, Organization, Status & Role)
- **Master Data:**
  - Companies — CRUD
  - Branches — CRUD
  - Parameters — CRUD (system enums: religion, marital_status, etc.)
- **Configuration:**
  - Roles — CRUD with permission assignment
  - Permissions — CRUD
  - Menus — CRUD (navigation management)
  - Activity Logs — view-only (audit trail)
- **Settings (User menu):**
  - Profile — view-only display + compact avatar upload (max 2MB: JPG/PNG/WebP/GIF)
  - Security — 2FA & password management

MenuSeeder only includes pages with actual implementation. Removed menu items: Sales, Purchasing, Inventory, CRM, Reports (no pages yet).

### UI Components
- Blade components: `resources/views/components/`
- Layouts: `resources/views/layouts/` (`app.blade.php`, `auth.blade.php`)
- Flux components: `resources/views/flux/` + inline Livewire components with `new class extends Component` syntax

### File Uploads & Media
- User avatars: handled by Spatie `MediaLibrary` via `User::InteractsWithMedia`. Collection: `avatar` (single file). Accepts: JPG, PNG, WebP, GIF. Max: 2MB
- File validation: `FileUploadValidationRules` trait provides `imageUploadRules()` (2MB default) and `documentUploadRules()` (5MB)
- Avatar display: `User::avatarUrl()` returns first media URL or null
- Profile settings page: compact avatar upload section with hint message showing format & size limits

### Testing
- Pest framework (not bare PHPUnit). Feature tests in `tests/Feature/`, unit tests in `tests/Unit/`
- `RefreshDatabase` is available but commented out in `tests/Pest.php` — uncomment to enable per-test DB resets
- `composer test` runs `config:clear` + `lint:check` + `php artisan test`

### Seeding & Data
- `RolesPermissionsSeeder` — creates 4 roles (superadmin, administrator, editor, staff) and permissions for active features. Superadmin grants all via `Gate::before()` in `AuthServiceProvider`
- `MenuSeeder` — creates sidebar/nav menus, syncs with permissions. Structure: Dashboard > Users > Master Data > Configuration > Profile (user menu)
- `ParameterSeeder` — creates system enums (religion, marital_status, etc.) as Parameter records with UUID keys
- `CountrySeeder`, `ProvinceSeeder`, `CitySeeder` — location hierarchy
- `UserSeeder` — demo users with different roles
- Run all: `php artisan migrate:fresh --seed`

### Key Configuration
- `config/fortify.php` — Fortify features, home path (`/dashboard`), 2FA settings
- `config/auth.php` — guards and password brokers
- `config/permission.php` — Spatie permission cache (24h default), table names
- `database/database.sqlite` — auto-created on setup

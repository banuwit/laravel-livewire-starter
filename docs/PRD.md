# Laravel Livewire Starter - Product Requirements Document

## 1. Project Overview

**Laravel Livewire Starter** is a modern, production-ready Laravel 13 starter kit designed as a foundation for building enterprise applications with essential features built-in. This template includes authentication, role-based access control, user management, and a responsive admin dashboard.

**Purpose:** Serve as a solid template for rapid application development with best practices, modern tooling, and essential features pre-configured.

---

## 2. Core Features

### 2.1 Authentication & Security
- **Registration & Login** — Fortify-based authentication with email verification
- **Password Management** — Secure password reset flow
- **Two-Factor Authentication (2FA)** — TOTP-based optional 2FA
- **Activity Logging** — Track user login/logout/failed attempts
- **Session Management** — Automatic session handling with logout support
- **Avatar Management** — User profile photo uploads with media library

### 2.2 Role-Based Access Control (RBAC)
- **Flexible Permission System** — Spatie Permission with resource.action naming convention
- **4 Built-in Roles:**
  - `superadmin` — Full access (via Gate::before)
  - `administrator` — Admin panel access
  - `editor` — Editor access
  - `staff` — Basic user access
- **Dynamic Role Assignment** — Assign/revoke roles per user
- **Permission Enforcement** — Middleware-based authorization on routes and Blade directives

### 2.3 User Management
- **User CRUD** — Create, read, update, delete users
- **Profile Management:**
  - Basic: name, email, phone, gender
  - Extended: identity number, religion, marital status, birth date
  - Address: street, country, province, city
  - Organization: company & branch assignment
- **View-Only Profile Page** — Users view their own profile (avatar upload only)
- **User Status** — Active/inactive toggle
- **Audit Trail** — Track who created/updated users and when

### 2.4 Admin Dashboard
- **Master Data Management:**
  - Companies — Organizational structure
  - Branches — Sub-organizational units
  - Parameters — System enums (religion, marital status, etc.)
  - Locations — Countries, provinces, cities
- **Configuration:**
  - Roles & Permissions management
  - Menu/Navigation management
  - Activity logs viewer
- **Clean Admin Interface** — Card-based layouts with Flux UI components

### 2.5 Data Organization
- **Location Hierarchy** — Countries → Provinces → Cities
- **Organizational Structure** — Companies → Branches
- **User-Profile Relationship** — 1-to-1 with soft delete support
- **Audit Fields** — `created_by`, `updated_by`, `deleted_by` tracking

---

## 3. Technical Stack

| Layer | Technology | Version | Purpose |
|-------|-----------|---------|---------|
| **Backend** | Laravel | 13.x | Web framework |
| **PHP** | PHP | ^8.3 | Language runtime |
| **Database** | SQLite | Default | Development; PostgreSQL/MySQL ready |
| **Frontend Framework** | Livewire | 4.x | Reactive component system |
| **UI Components** | Flux | 2.x | Pre-built UI library |
| **Styling** | Tailwind CSS | 4.x | Utility-first CSS framework |
| **Auth** | Laravel Fortify | Latest | Authentication scaffolding |
| **RBAC** | Spatie Permission | Latest | Role & permission management |
| **Media** | Spatie Media Library | Latest | File/avatar uploads |
| **Audit** | Spatie Activity Log | Latest | Activity tracking |
| **Testing** | Pest | 4.x | Modern testing framework |
| **Code Quality** | Pint | Latest | Code formatting & linting |
| **JS Runtime** | Node.js | ^18+ | Frontend tooling |
| **Build Tool** | Vite | Latest | Fast module bundler |

---

## 4. Architecture & Design Patterns

### 4.1 Directory Structure

```
laravel-livewire-starter/
├── app/
│   ├── Actions/              # Fortify custom actions
│   ├── Concerns/             # Reusable traits (validation, file upload)
│   ├── Models/               # Eloquent models
│   ├── Providers/            # Service providers
│   └── Notifications/        # User notifications
├── database/
│   ├── migrations/           # Database schema
│   ├── seeders/              # Database seeders
│   └── factories/            # Model factories
├── resources/
│   ├── views/
│   │   ├── layouts/          # Master layouts
│   │   ├── pages/            # Page components (auth, settings, admin)
│   │   ├── components/       # Blade components
│   │   └── flux/             # Custom Flux extensions
│   └── css/                  # Tailwind styles
├── routes/
│   ├── web.php               # Main routes
│   └── settings.php          # Settings routes
├── tests/                    # Pest test suite
├── docs/                     # Project documentation
└── CLAUDE.md                 # AI assistant guidance

```

### 4.2 Component Pattern

Uses inline Livewire components with `new class extends Component` syntax:
```php
new class extends Component {
    // Component logic
};
?>
<!-- Template -->
```

Benefits:
- Single file per component
- Cleaner organization
- Easier to locate logic & view together

### 4.3 State Management

- **Form State** — Livewire properties with validation
- **Computed Properties** — `#[Computed]` for derived values
- **Live Updates** — `wire:model.live` for real-time reactivity
- **Database Transaction** — `DB::transaction()` for data consistency

### 4.4 Authorization Strategy

**Gate-based Authorization:**
```php
// Superadmin bypass
Gate::before(fn($user) => $user->hasRole('superadmin') ? true : null);

// Permission check
Auth::user()->can('users.edit')
@can('users.view') ... @endcan
```

**Middleware Enforcement:**
```php
Route::middleware('permission:users.view')->get('users', ...)
```

---

## 5. Database Schema Overview

### Core Tables

| Table | Purpose | Key Relationships |
|-------|---------|-------------------|
| `users` | User accounts | hasOne Profile, belongsTo Company/Branch, hasMany Roles |
| `profiles` | Extended user info | belongsTo User, Religion/MaritalStatus (Parameter), Location |
| `roles` | Role definitions | belongsToMany Users, Permissions |
| `permissions` | Permission definitions | belongsToMany Roles |
| `companies` | Organizations | hasMany Branches, Users |
| `branches` | Organization sub-units | belongsTo Company, hasMany Users |
| `parameters` | System enums | (religion, marital_status, etc.) |
| `countries` | Location data | hasMany Provinces |
| `provinces` | Location data | belongsTo Country, hasMany Cities |
| `cities` | Location data | belongsTo Province |
| `menus` | Navigation structure | self-referential (parent_id) |
| `activity_log` | Audit trail | causedBy, subject polymorphic |
| `media` | File uploads | polymorphic (users avatars) |

### Key Features

- **Soft Deletes** — Users & Profiles support soft delete with `deleted_at` & `deleted_by`
- **Audit Fields** — `created_by`, `updated_by`, `deleted_by` track user actions
- **Foreign Keys** — Cascading deletes where appropriate
- **Timestamps** — `created_at`, `updated_at` on all tables

---

## 6. Features Summary

### 6.1 Admin Pages

| Page | Features | Permissions |
|------|----------|-------------|
| **Dashboard** | Overview (stub) | `dashboard.view` |
| **Users** | CRUD, role assignment, status toggle | `users.*` |
| **Companies** | CRUD | `companies.*` |
| **Branches** | CRUD (linked to company) | `branches.*` |
| **Parameters** | CRUD (system enums) | `parameters.*` |
| **Roles** | CRUD, permission assignment | `roles.*` |
| **Permissions** | CRUD | `permissions.*` |
| **Menus** | CRUD (navigation) | `menus.*` |
| **Activity Logs** | View-only audit trail | `activity_logs.view` |

### 6.2 User Pages

| Page | Features |
|------|----------|
| **Profile** | View-only display + avatar upload (2MB max) |
| **Security** | Password change, 2FA setup |
| **Settings** | Redirect to profile |

---

## 7. Setup & Installation

### Quick Start

```bash
# Clone repository
git clone <repo-url>
cd laravel-livewire-starter

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Development
composer dev
# Runs: Laravel server + queue worker + Vite

# Testing
composer test
# Runs: lint:check + tests
```

### Database Seeding

```bash
# Seed with demo data
php artisan migrate:fresh --seed

# Seeders executed:
# - RolesPermissionsSeeder (4 roles, 19 permissions)
# - MenuSeeder (navigation structure)
# - ParameterSeeder (religion, marital_status)
# - CountrySeeder / ProvinceSeeder / CitySeeder
# - UserSeeder (demo users with different roles)
```

### Demo Credentials

After seeding, demo users are available:
- **Email:** `admin@example.com` (admin role)
- **Email:** `user@example.com` (staff role)
- **Password:** Check `.env` or seeder file

---

## 8. Key Implementation Details

### 8.1 User & Profile Creation

When creating a user via admin panel:
1. User record created with basic fields (name, email, phone, gender, company, branch)
2. Profile record automatically created if extended data provided
3. Role assigned (optional)
4. Activity logged

```php
// resources/views/pages/users/⚡create.blade.php
DB::transaction(function () {
    $user = User::create([...]);
    if ($hasProfileData) {
        $user->profile()->create([...]);
    }
    if ($selectedRole) {
        $user->assignRole($role);
    }
});
```

### 8.2 Avatar Upload

- **Max Size:** 2MB
- **Formats:** JPG, PNG, WebP, GIF
- **Stored via:** Spatie MediaLibrary in `avatar` collection
- **Retrieval:** `User::avatarUrl()` returns media URL or null
- **Location:** Profile settings page (compact section)

### 8.3 Permission Enforcement

**Route-level:**
```php
Route::middleware('permission:users.view')->get('users', ...)
```

**Blade-level:**
```blade
@can('users.edit')
    <flux:button>Edit User</flux:button>
@endcan
```

**Code-level:**
```php
Auth::user()->can('users.delete')  // Boolean
$user->hasPermissionTo('users.edit')  // Via Spatie
```

### 8.4 Activity Logging

Automatically logged events:
- User login/logout
- Failed login attempts
- User creation/update/delete
- Model changes (via LogsActivity trait)

Access via: Admin → Configuration → Activity Logs

---

## 9. File Upload & Media Handling

### Validation Rules

```php
// Images: max 2MB
$this->imageUploadRules()  // Returns: required, image, mimes:jpg,jpeg,png,webp,gif, max:2048

// Documents: max 5MB
$this->documentUploadRules()  // Returns: nullable, file, mimes:pdf,doc,docx,xls,xlsx, max:5120
```

### Avatar Implementation

```php
// User model
class User extends Model {
    use InteractsWithMedia;
    
    public function registerMediaCollections(): void {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }
    
    public function avatarUrl(): ?string {
        return $this->getFirstMediaUrl('avatar') ?: null;
    }
}
```

---

## 10. Security Considerations

### Built-in Protections

✅ **CSRF Protection** — Laravel middleware
✅ **Password Hashing** — bcrypt via Fortify
✅ **SQL Injection Prevention** — Eloquent parameterized queries
✅ **XSS Prevention** — Blade escaping by default
✅ **Rate Limiting** — Available via middleware
✅ **HTTPS Ready** — Configured for production
✅ **Two-Factor Auth** — Optional TOTP setup
✅ **Audit Logging** — Track sensitive actions

### Recommended Additional Security

For production deployment:
- [ ] Enable HTTPS/SSL certificates
- [ ] Configure environment variables securely
- [ ] Use database password protection
- [ ] Enable rate limiting on auth endpoints
- [ ] Review & lock down S3/CDN access if using media storage
- [ ] Regular security updates: `composer update`
- [ ] Implement backup strategy

---

## 11. Performance Considerations

### Current Optimizations

✅ **Eager Loading** — Prevent N+1 queries (profile relationships, company/branch)
✅ **Query Optimization** — Indexed foreign keys, selective column selection
✅ **Caching** — Spatie Permission cache (24h default)
✅ **Pagination** — Default 10 items per page in tables
✅ **Media Library** — Efficient URL generation


---

## 12. Testing Strategy

### Current Setup

- **Framework:** Pest v4
- **Location:** `tests/Feature/` and `tests/Unit/`
- **Database:** `RefreshDatabase` trait (commented, uncomment per-test if needed)
- **Run:** `composer test` (includes lint:check)

### Test Examples to Add

```php
// Authentication flow
test('user can register')
test('user can login')
test('unauthorized user cannot access admin')

// User management
test('admin can create user')
test('admin cannot create duplicate email')
test('user permissions are enforced')

// Data integrity
test('profile is created with user')
test('soft deletes work correctly')
test('audit logs are recorded')
```

---

## 13. Deployment Guide

### Production Checklist

- [ ] `.env` configured for production (DB, APP_KEY, MAIL settings)
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Database migrated: `php artisan migrate --force`
- [ ] Database seeded: `php artisan db:seed --force`
- [ ] Storage linked: `php artisan storage:link`
- [ ] Permissions set: `php artisan optimize`
- [ ] SSL certificate installed
- [ ] Cron job for queue worker (if using async jobs)
- [ ] Backup strategy in place

### Environment Variables Template

```env
APP_NAME="Laravel Starter"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=starter_db
DB_USERNAME=user
DB_PASSWORD=password

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@your-domain.com

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 14. Troubleshooting

### Common Issues

**"No such column: profiles.deleted_at"**
- Solution: Run migrations `php artisan migrate`

**Permission denied on storage**
- Solution: `chmod -R 775 storage bootstrap/cache`

**Queue worker not processing**
- Solution: Check `.env` QUEUE_CONNECTION, start worker: `php artisan queue:work`

**Avatar not uploading**
- Solution: Check storage disk permissions, verify max file size in `php.ini`

### Useful Commands

```bash
# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Database debugging
php artisan tinker
php artisan migrate:refresh
php artisan migrate:fresh --seed

# Queue debugging
php artisan queue:work --tries=3
php artisan failed-jobs

# Code quality
composer lint:check
composer lint
composer test
```

---

## 15. Contributing Guidelines

### Code Standards

- **Formatting:** Pint (`composer lint`)
- **Testing:** Pest test cases required for features
- **Documentation:** Update CLAUDE.md for architectural changes
- **Commits:** Clear messages describing what & why

### Before Submitting

```bash
# Run linting
composer lint

# Run tests
composer test

# Check CLAUDE.md for accuracy
```

---

## 16. Appendix A: Glossary

| Term | Definition |
|------|-----------|
| **RBAC** | Role-Based Access Control |
| **2FA** | Two-Factor Authentication |
| **Fortify** | Laravel's authentication scaffolding package |
| **Livewire** | Full-stack framework for building reactive components |
| **Flux** | UI component library built on Livewire |
| **MediaLibrary** | Spatie package for handling file uploads |
| **Activity Log** | Spatie package for audit trailing |
| **Soft Delete** | Logical deletion (marking deleted without removing) |
| **N+1 Query** | Performance problem where multiple queries execute instead of single JOIN |
| **Eager Loading** | Preloading relationships to prevent N+1 queries |

---

## 17. Appendix B: Useful Resources

- [Laravel Docs](https://laravel.com/docs)
- [Livewire Docs](https://livewire.laravel.com/docs)
- [Flux UI Docs](https://fluxui.dev/docs)
- [Spatie Permission](https://spatie.be/docs/laravel-permission)
- [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary)
- [Pest Testing](https://pestphp.com)

---

**Document Version:** 1.0  
**Last Updated:** June 2026  
**Project Status:** Production-Ready Template

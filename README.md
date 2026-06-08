# Laravel Livewire Starter

A production-ready Laravel 13 starter kit with authentication, role-based access control, real-time features, and a collection of AI agent skills — designed as a solid foundation for building full-featured web applications.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 + PHP 8.3 |
| Auth | Laravel Fortify (login, register, 2FA, password reset) |
| UI | Livewire 4.x + Flux 2.x + Tailwind CSS 4.x |
| RBAC | Spatie Laravel Permission |
| Activity Log | Spatie Laravel Activitylog |
| File Uploads | Spatie Laravel Medialibrary |
| Real-time | Laravel Reverb (WebSocket) |
| Charts | Chart.js |
| Testing | Pest v4 |
| Linting | Laravel Pint |
| Database | SQLite (default, easily swappable) |

---

## Features

- **Authentication** — Registration, login, email verification, password reset, two-factor authentication (2FA)
- **Role & Permission Management** — Seeded roles (`superadmin`, `administrator`, `editor`, `staff`) with Spatie Permission, `<resource>.<action>` permission convention
- **User Management** — Full CRUD with extended profile (identity, religion, marital status, address, location hierarchy), avatar upload, role assignment
- **Organization & Branch** — Multi-level organizational hierarchy linked to users
- **Master Data** — Parameters (system enums), Locations (Country → Province → City)
- **Menu Management** — Dynamic sidebar/nav with permission-based visibility
- **Activity Logs** — Audit trail for all model changes
- **Notification System** — Real-time bell notifications via Reverb/WebSocket
- **Settings** — Profile view, avatar upload, security (2FA + password change)

---

## Quick Start

```bash
# Clone the repository
git clone <repo-url>
cd laravel-livewire-starter

# Install all dependencies, generate key, migrate, build frontend
composer setup

# Seed the database with demo data
php artisan migrate:fresh --seed

# Start the development server (Laravel + Queue + Vite + Reverb)
composer dev
```

Open `http://localhost:8000` — login with `projectadmin@wit.id` / `demoadmin123*#`

---

## Commands

```bash
composer setup        # One-time setup: install deps, .env, key, migrate, npm install & build
composer dev          # Start all dev processes concurrently (server, queue, vite, reverb)
composer test         # config:clear + lint check + run Pest tests
composer lint         # Auto-fix code style with Pint
composer lint:check   # Check code style without fixing

php artisan migrate:fresh --seed   # Reset database and re-seed all data
./vendor/bin/pest --filter="test_name"  # Run a single test
```

---

## Folder Structure

```
laravel-livewire-starter/
├── .agents/                        # AI agent skills (see Agent Skills section)
│   └── skills/
│       ├── laravel-specialist/     # Laravel/Livewire/Eloquent specialist
│       ├── mysql/                  # MySQL query optimization
│       ├── postgres/               # PostgreSQL reference
│       ├── frontend-design/        # Frontend design patterns
│       ├── tailwind-design-system/ # Tailwind component system
│       ├── ui-ux-pro-max/          # UI/UX design assistant
│       ├── webapp-testing/         # Web app testing (Playwright)
│       ├── systematic-debugging/   # Structured debugging methodology
│       ├── brainstorming/          # Feature brainstorming assistant
│       ├── writing-plans/          # Implementation planning
│       ├── requesting-code-review/ # Code review assistant
│       ├── improve-codebase-architecture/ # Architecture review
│       ├── to-prd/                 # Generate PRD from ideas
│       ├── grill-me/               # Challenge your assumptions
│       └── using-superpowers/      # Guide to using all skills
│
├── app/
│   ├── Actions/Fortify/            # User creation & password reset logic
│   ├── Concerns/                   # Reusable validation traits
│   │   ├── HasFileUpload.php
│   │   ├── FileUploadValidationRules.php
│   │   ├── PasswordValidationRules.php
│   │   └── ProfileValidationRules.php
│   ├── Models/                     # Eloquent models
│   │   ├── User.php                # Auth + HasRoles + HasMedia + SoftDeletes
│   │   ├── Profile.php             # 1:1 extended user data
│   │   ├── Organization.php        # Organization/company entity
│   │   ├── Branch.php              # Branch under Organization
│   │   ├── Parameter.php           # System enums (religion, marital status, etc.)
│   │   ├── Menu.php                # Navigation menu
│   │   ├── Country/Province/City.php # Location hierarchy
│   │   ├── Role.php / Permission.php # Spatie models
│   │   └── ActivityLog.php
│   ├── Notifications/              # Notification classes
│   └── Providers/                  # Service providers (AuthServiceProvider for Gate)
│
├── database/
│   ├── factories/                  # Model factories (UserFactory with withProfile state)
│   ├── migrations/                 # Ordered migrations (0001_, 0002_ prefix for control)
│   └── seeders/
│       ├── DatabaseSeeder.php      # Master seeder — calls all others
│       ├── RolesPermissionsSeeder.php
│       ├── MenuSeeder.php
│       ├── ParameterSeeder.php
│       └── LocationSeeder.php      # Country → Province → City
│
├── docs/
│   ├── PRD.md                      # Full product requirements document
│   └── guides/
│       ├── roles-permissions-menus.md
│       ├── activity-logging.md
│       ├── FILE_UPLOAD_SYSTEM.md
│       ├── NOTIFICATION_SYSTEM.md
│       └── parameter-feature.md
│
├── resources/views/
│   ├── components/                 # Blade components
│   ├── layouts/                    # app.blade.php, auth.blade.php
│   ├── flux/                       # Flux component overrides
│   └── pages/                      # Livewire full-page components
│       ├── auth/                   # Login, register, 2FA, password reset
│       ├── dashboard.blade.php
│       ├── users/                  # index, create, edit, roles
│       ├── organizations/          # index, create, edit
│       ├── branches/               # index, create, edit
│       ├── parameters/             # index (configurations)
│       ├── roles/                  # index, create, edit
│       ├── menus/                  # index, create, edit
│       ├── activity-logs/          # index (view-only)
│       ├── notifications/          # index
│       └── settings/               # profile, security, appearance
│
├── routes/
│   ├── web.php                     # Main routes
│   └── settings.php                # Settings sub-routes
│
└── CLAUDE.md                       # AI assistant guide for this codebase
```

---

## Architecture

### Authentication

Built on **Laravel Fortify** with no controllers to maintain — all auth logic lives in `app/Actions/Fortify/`. The `User` model extends `TwoFactorAuthenticatable` and the home redirect after login is `/dashboard`.

### Livewire Component Pattern

All pages use **inline Livewire components** (single-file components) — PHP class and Blade template live in the same `.blade.php` file:

```php
<?php
// resources/views/pages/users/⚡index.blade.php

use App\Models\User;
use Livewire\Component;

new class extends Component {
    public string $search = '';

    public function render()
    {
        return $this->view(['users' => User::paginate(10)]);
    }
};
?>

<div>
    {{-- Blade template here --}}
</div>
```

Files are prefixed with `⚡` to visually distinguish Livewire pages from plain Blade files.

### Role & Permission Convention

Permissions follow `<resource>.<action>` naming:

```php
// Examples
'users.view'        'users.create'      'users.edit'        'users.delete'
'users.assign_roles'
'roles.view'        'organizations.create'   'branches.edit'
'activity_logs.view'  'activity_logs.export'
```

Roles:
- `superadmin` — bypasses all checks via `Gate::before()` in `AuthServiceProvider`
- `administrator` — full access to all admin features
- `editor` / `staff` — dashboard access only (extend as needed)

Check permissions in Blade with `@can` / `@canany`. Check in PHP with `$user->can()`.

### Adding a New Resource

1. **Migration** — add to `database/migrations/` with `softDeletes()`, `deleted_by`, `created_by`, `updated_by`
2. **Model** — extend the pattern from `Organization.php` (SoftDeletes, LogsActivity, booted hooks)
3. **Seeder** — add permissions to `RolesPermissionsSeeder.php` and menu entry to `MenuSeeder.php`
4. **Routes** — add to `routes/web.php` with `permission:` middleware
5. **Views** — create `resources/views/pages/<resource>/⚡index.blade.php`, `⚡create.blade.php`, `⚡edit.blade.php`

### Audit Trail Pattern

Every user-managed model records who created, updated, and deleted each record:

```php
// Migration
$table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
$table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
$table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
$table->softDeletes();
```

```php
// Model — booted() hook auto-fills these fields
static::creating(fn ($m) => $m->created_by = auth()->id());
static::updating(fn ($m) => $m->updated_by = auth()->id());
static::deleting(fn ($m) => $m->deleted_by = auth()->id());
```

### File Uploads

Avatar uploads use **Spatie MediaLibrary**. For new upload needs, use the provided traits:

```php
use App\Concerns\HasFileUpload;
use App\Concerns\FileUploadValidationRules;

// Image: max 2MB, accepts JPG/PNG/WebP/GIF
$this->validate($this->imageUploadRules());

// Document: max 5MB
$this->validate($this->documentUploadRules());
```

---

## Agent Skills

This project ships with a curated set of AI agent skills in `.agents/skills/`. When working with Claude Code (or compatible AI assistants), these skills activate specialist knowledge for specific tasks.

### Available Skills

| Skill | Purpose |
|---|---|
| `laravel-specialist` | Laravel, Eloquent, Livewire, Queues, Pest testing |
| `mysql` | Query optimization, indexes, deadlocks, N+1 detection |
| `postgres` | PostgreSQL schema design, MVCC, indexing, pgBouncer |
| `frontend-design` | Frontend design patterns and best practices |
| `tailwind-design-system` | Tailwind component patterns and design tokens |
| `ui-ux-pro-max` | UI/UX design assistant with visual feedback |
| `webapp-testing` | Browser testing with Playwright (visual regression, E2E) |
| `systematic-debugging` | Structured root-cause debugging methodology |
| `brainstorming` | Visual brainstorming for new features |
| `writing-plans` | Implementation planning before coding |
| `requesting-code-review` | Structured code review with inline comments |
| `improve-codebase-architecture` | Architecture audit and improvement suggestions |
| `to-prd` | Transform ideas into Product Requirements Documents |
| `grill-me` | Challenge your technical decisions and assumptions |
| `using-superpowers` | Guide to using all skills effectively |

### How Skills Work

Skills are Markdown files that provide context and instructions to the AI assistant. They're stored in `.agents/skills/<skill-name>/SKILL.md` and are automatically available to Claude Code.

**In Claude Code**, invoke a skill via the `/` command or the `Skill` tool. The assistant will load the skill's knowledge and follow its workflow.

```
# Examples of when each skill activates:
"Add a new Eloquent model with relationships" → laravel-specialist
"Why is this query slow?" → mysql or systematic-debugging
"Design a new dashboard card" → ui-ux-pro-max + tailwind-design-system
"Write E2E tests for the login flow" → webapp-testing
"I want to add a sales module" → brainstorming → to-prd → writing-plans
"Review this PR before merge" → requesting-code-review
```

### How to Add a New Skill

```bash
mkdir .agents/skills/my-skill
# Create SKILL.md with frontmatter: name, description, triggers
# Optionally add references/ folder with detailed topic docs
```

---

## Demo Users (after seeding)

| Email | Password | Role |
|---|---|---|
| `projectadmin@wit.id` | `demoadmin123*#` | superadmin |
| (36 random users) | — | staff |

---

## Testing

```bash
composer test                                      # Full test suite
./vendor/bin/pest --filter="registration"          # Single test by name
./vendor/bin/pest tests/Feature/Auth/              # All tests in a folder
```

Tests live in `tests/Feature/` and `tests/Unit/`. The framework is **Pest v4** — use its expressive syntax:

```php
it('redirects guests to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('allows admin to create a user', function () {
    $admin = User::factory()->create()->assignRole('administrator');
    $this->actingAs($admin)->post('/users', [...])
        ->assertRedirect('/users');
});
```

---

## Documentation

Detailed guides are in `docs/guides/`:

- [Roles, Permissions & Menus](docs/guides/roles-permissions-menus.md)
- [Activity Logging](docs/guides/activity-logging.md)
- [File Upload System](docs/guides/FILE_UPLOAD_SYSTEM.md)
- [Notification System](docs/guides/NOTIFICATION_SYSTEM.md)
- [Parameter / System Enum Feature](docs/guides/parameter-feature.md)
- [Product Requirements Document](docs/PRD.md)

AI development guide: [CLAUDE.md](CLAUDE.md)

---

## License

MIT

# Activity Logging Guide

## Pendahuluan

Activity Logging adalah fitur yang mencatat semua aktivitas penting dalam aplikasi, termasuk:
- **Auth Events**: Login, logout, failed login attempts
- **Model Changes**: Create, update, delete pada User, Company, Branch, Role, Permission

Fitur ini menggunakan package `spatie/laravel-activitylog` v5.0.0 dan menyimpan semua log ke database table `activity_log`.

---

## Apa itu Activity Log?

Activity Log adalah catatan dari setiap aksi yang dilakukan oleh user dalam aplikasi. Setiap log mencakup:

| Field | Deskripsi |
|-------|-----------|
| `id` | Primary key unik untuk setiap log entry |
| `log_name` | Jenis log: `auth` (auth events) atau `model` (data changes) |
| `description` | Deskripsi readable dari aksi: "User created", "login", dll |
| `event` | Nama event: `created`, `updated`, `deleted`, `login`, `logout`, `failed_login` |
| `subject_type` | Class name dari model yang berubah (e.g., `App\Models\User`) |
| `subject_id` | ID dari model yang berubah |
| `causer_type` | Class dari user yang melakukan aksi (selalu `App\Models\User`) |
| `causer_id` | ID dari user yang melakukan aksi |
| `properties` | JSON object berisi: `attributes` (new values), `old` (old values), `ip`, `email`, `user_agent` |
| `attribute_changes` | JSON object berisi structured before/after diff |
| `created_at` | Timestamp kapan aksi terjadi |
| `updated_at` | Timestamp last update (biasanya sama dengan created_at) |

### Contoh Activity Log Entry

```json
{
  "id": 1,
  "log_name": "model",
  "description": "User updated",
  "event": "updated",
  "subject_type": "App\\Models\\User",
  "subject_id": 5,
  "causer_type": "App\\Models\\User",
  "causer_id": 1,
  "properties": {
    "old": {
      "name": "Old Name",
      "email": "old@example.com",
      "is_active": true
    },
    "attributes": {
      "name": "New Name",
      "email": "new@example.com",
      "is_active": false
    }
  },
  "created_at": "2026-05-29 10:30:45"
}
```

---

## Struktur Folder & File

```
laravel-livewire-starter/
├── app/Models/
│   ├── ActivityLog.php                 ← Custom Activity model
│   ├── Role.php                        ← Local Role override (untuk LogsActivity)
│   ├── User.php                        ← User dengan LogsActivity trait
│   ├── Company.php                     ← Company dengan LogsActivity trait
│   ├── Branch.php                      ← Branch dengan LogsActivity trait
│   └── Permission.php                  ← Permission dengan LogsActivity trait
│
├── app/Providers/
│   └── AppServiceProvider.php          ← Auth event listeners (Login, Logout, Failed)
│
├── config/
│   ├── activitylog.php                 ← Spatie activity log configuration
│   └── permission.php                  ← Spatie permission config (dengan role override)
│
├── database/
│   ├── migrations/
│   │   └── 2026_05_26_000000_create_activity_log_table.php
│   └── seeders/
│       ├── RolesPermissionsSeeder.php  ← Added activity_logs permissions
│       └── MenuSeeder.php              ← Added Activity Logs menu item
│
├── routes/
│   └── web.php                         ← Added /activity-logs route
│
├── resources/views/pages/
│   └── activity-logs/
│       └── ⚡index.blade.php           ← Activity logs Livewire page
│
└── docs/guides/
    └── activity-logging.md             ← Dokumentasi ini
```

---

## Implementasi Code

### 1. Installation & Setup

```bash
# Install package
composer require spatie/laravel-activitylog

# Migrate
php artisan migrate

# Seed (untuk permissions dan menu)
php artisan db:seed --class=RolesPermissionsSeeder
php artisan db:seed --class=MenuSeeder
php artisan permission:cache-reset
```

### 2. Custom Activity Model

**`app/Models/ActivityLog.php`**

```php
<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as BaseActivity;

class ActivityLog extends BaseActivity
{
    // Parse before/after changes dari properties
    public function getChangesAttribute(): array
    {
        $old = $this->properties->get('old', []);
        $new = $this->properties->get('attributes', []);

        if (empty($old) && empty($new)) {
            return [];
        }

        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        $changes = [];

        foreach ($keys as $key) {
            $before = $old[$key] ?? null;
            $after  = $new[$key] ?? null;

            if ($before !== $after) {
                $changes[] = [
                    'field'  => $key,
                    'before' => $before,
                    'after'  => $after,
                ];
            }
        }

        return $changes;
    }

    // Helper untuk display subject type (e.g., "User" instead of "App\Models\User")
    public function subjectLabel(): string
    {
        if (!$this->subject_type) {
            return '—';
        }
        return class_basename($this->subject_type);
    }
}
```

### 3. Add LogsActivity ke Models

**Contoh: `app/Models/User.php`**

```php
<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'is_active', 'company_id', 'branch_id', 'gender_id'])
            ->logOnlyDirty()                              // Hanya log field yang berubah
            ->dontLogEmptyChanges()                       // Jangan log jika tidak ada perubahan
            ->useLogName('model')                         // Set log name
            ->setDescriptionForEvent(fn (string $e) => "User {$e}");  // Custom description
    }
}
```

**Penjelasan LogOptions:**
- `logOnly(['field1', 'field2'])` - Hanya log field tertentu
- `logOnlyDirty()` - Hanya log field yang actually berubah
- `dontLogEmptyChanges()` - Prevent logs tanpa perubahan
- `useLogName('model')` - Set log_name di database
- `setDescriptionForEvent()` - Customize deskripsi

### 4. Auth Event Listeners

**`app/Providers/AppServiceProvider.php`**

```php
<?php

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Log login events
        Event::listen(Login::class, function (Login $event) {
            activity('auth')
                ->causedBy($event->user)
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ])
                ->log('login');
        });

        // Log logout events
        Event::listen(Logout::class, function (Logout $event) {
            activity('auth')
                ->causedBy($event->user)
                ->withProperties(['ip' => request()->ip()])
                ->log('logout');
        });

        // Log failed login attempts
        Event::listen(Failed::class, function (Failed $event) {
            $builder = activity('auth')
                ->withProperties([
                    'ip' => request()->ip(),
                    'email' => $event->credentials['email'] ?? null,
                    'user_agent' => request()->userAgent(),
                ]);

            if ($event->user) {
                $builder->causedBy($event->user);
            }

            $builder->log('failed_login');
        });
    }
}
```

**Penjelasan:**
- `activity('auth')` - Create activity dengan log_name='auth'
- `->causedBy($user)` - Set user yang melakukan aksi
- `->withProperties([...])` - Tambah custom properties (IP, user agent, email)
- `->log('action_name')` - Simpan dengan deskripsi action

### 5. Permission & Menu Setup

**`database/seeders/RolesPermissionsSeeder.php`**

```php
$resources = [
    // ... other resources ...
    
    // Activity Logs (view-only + export)
    'activity_logs' => ['view', 'export'],
];

// Assign ke administrator role
Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web'])
    ->givePermissionTo($permsFor([
        'dashboard',
        'users', 'roles', 'menus', 'profiles', 'parameters', 'companies', 'branches',
        'activity_logs',  // ← Tambah ini
        'reports_sales', 'reports_purchasing', 'reports_inventory',
        'reports_transaction', 'reports_crm', 'reports_financial',
    ]));
```

**`database/seeders/MenuSeeder.php`**

```php
// Tambah di Configuration group
$actLogs = $this->child(
    'activity-logs', 
    'Activity Logs', 
    'clock', 
    $configuration, 
    3, 
    'activity-logs.index', 
    'activity-logs.*'
);
$this->attachPerms($actLogs, [
    'activity_logs.view', 
    'activity_logs.export',
]);
```

### 6. Route & Livewire Page

**`routes/web.php`**

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // ... other routes ...
    
    Route::livewire('activity-logs', 'pages::activity-logs.index')
        ->middleware('permission:activity_logs.view')
        ->name('activity-logs.index');
});
```

**`resources/views/pages/activity-logs/⚡index.blade.php`**

Component Livewire dengan:
- **Filters**: search, log type (auth/model), subject type, user, date range
- **Table**: #, Date, Causer, Event, Log Type, Subject, Description, Actions
- **Detail Modal**: shows before/after changes + IP/user agent
- **Sorting**: Date, Event, Log Type (ascending/descending)

---

## Cara Kerja Activity Logging

### 1. Model Changes (Create, Update, Delete)

Ketika Anda membuat/edit/delete model dengan LogsActivity trait:

```php
// Create
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);
// → Otomatis log: log_name='model', event='created', description='User created'

// Update
$user->update(['name' => 'Jane Doe']);
// → Otomatis log: log_name='model', event='updated', description='User updated'
// → properties.old = ['name' => 'John Doe']
// → properties.attributes = ['name' => 'Jane Doe']

// Delete
$user->delete();
// → Otomatis log: log_name='model', event='deleted', description='User deleted'
```

### 2. Auth Events (Login, Logout, Failed)

Ketika user login/logout:

```
User login → Laravel fires Login event → Listener menangkap → Log ke activity_log
  {
    log_name: 'auth',
    event: 'login',
    causer_id: 1,
    properties: { ip: '192.168.1.1', user_agent: 'Mozilla...' }
  }
```

### 3. View Activity Logs

Akses `/activity-logs` (hanya yang punya `activity_logs.view` permission):

```
Table menampilkan:
├── Date: 2026-05-29 10:30:45
├── Causer: John Doe (john@example.com)
├── Event: updated (warna badge: sky)
├── Log Type: model (badge blue)
├── Subject: User #5
├── Description: User updated
└── Actions: [eye icon] → buka detail modal
    
Detail Modal:
├── Causer: John Doe
├── Event: updated
├── Subject: User #5
├── Changes Table:
   ├── Field | Before | After
   ├── name | Old Name | New Name
   └── email | old@example.com | new@example.com
```

---

## Configuration

### File: `config/activitylog.php`

```php
return [
    'enabled' => env('ACTIVITYLOG_ENABLED', true),      // Enable/disable logging
    'clean_after_days' => 365,                          // Auto-delete logs older than 365 days
    'default_log_name' => 'default',                    // Default log name
    'activity_model' => App\Models\ActivityLog::class,  // Custom Activity model
    'default_except_attributes' => [],                  // Exclude attributes globally
    'buffer' => [
        'enabled' => env('ACTIVITYLOG_BUFFER_ENABLED', false),  // Buffer writes
    ],
];
```

---

## Best Practices

### 1. Choosing What to Log

✅ **DO log:**
- User create/update/delete
- Company/Branch data changes
- Permission/Role modifications
- Important financial/inventory transactions
- Authentication events

❌ **DON'T log:**
- Sensitive data (passwords, tokens)
- Frequently changing cache fields
- Timestamp-only updates
- System-generated fields

### 2. LogOptions Configuration

```php
// ✅ GOOD: Log specific important fields only
LogOptions::defaults()
    ->logOnly(['name', 'email', 'status', 'is_active'])
    ->logOnlyDirty()
    ->dontLogEmptyChanges()

// ❌ BAD: Log semua field (verbose, noise)
LogOptions::defaults()->logAll()

// ❌ BAD: No dirty check (log bahkan jika tidak ada perubahan)
LogOptions::defaults()->logOnly(['name', 'email'])  // tanpa logOnlyDirty()
```

### 3. Performance Considerations

- Logs disimpan synchronously (dalam same request)
- Untuk high-traffic, bisa enable `buffer` untuk batch writes
- Pergunakan `clean_after_days` untuk auto-delete old logs
- Index fields yang sering di-filter: `log_name`, `causer_id`, `created_at`

---

## Querying Activity Logs

### Via Code

```php
use App\Models\ActivityLog;

// Semua logs
$logs = ActivityLog::all();

// Logs untuk user tertentu
$logs = ActivityLog::where('causer_id', 1)->get();

// Logs untuk model tertentu
$logs = ActivityLog::where('subject_type', User::class)
    ->where('subject_id', 5)
    ->get();

// Auth events only
$logs = ActivityLog::where('log_name', 'auth')->get();

// Login events
$logs = ActivityLog::where('log_name', 'auth')
    ->where('event', 'login')
    ->get();

// Dengan causer (user yang melakukan aksi)
$logs = ActivityLog::with('causer')->get();
foreach ($logs as $log) {
    echo $log->causer->name;  // User yang melakukan aksi
}

// Changes accessor
$log = ActivityLog::first();
$changes = $log->changes;  // Array of [field => before/after]
```

### Via UI

Visit `/activity-logs` dan gunakan:
- **Search**: Cari description atau event name
- **Log Type Filter**: Auth / Model
- **Subject Type Filter**: User / Company / Branch / Role / Permission
- **User Filter**: Filter by causer (user yang melakukan aksi)
- **Date Range**: Filter by created_at
- **Sorting**: Click column header untuk sort

---

## Troubleshooting

### Problem: Activity logs tidak terbuat

**Solusi:**
1. Pastikan model menggunakan LogsActivity trait
2. Check `config/activitylog.php` - `enabled` harus `true`
3. Run `php artisan migrate` untuk create table
4. Check bahwa LogOptions methods valid (v5 API)

### Problem: Logs terlalu banyak/noisy

**Solusi:**
1. Gunakan `logOnly(['field1', 'field2'])` untuk log specific fields saja
2. Gunakan `dontLogEmptyChanges()` untuk skip empty logs
3. Enable `clean_after_days` untuk auto-delete old logs
4. Disable logging untuk fields yang tidak penting

### Problem: Activity Log tidak muncul di sidebar

**Solusi:**
1. Run seeder: `php artisan db:seed --class=MenuSeeder`
2. User harus punya `activity_logs.view` permission
3. Check `config/permission.php` bahwa role di-assign dengan permission

---

## Related Documentation

- [Spatie Activity Log v5 Docs](https://spatie.be/docs/laravel-activitylog/v5/installation-and-setup)
- [Permission & Role Guide](./permission-system.md) *(jika ada)*
- [Livewire Documentation](https://livewire.laravel.com/)

---

## Summary

Activity Logging membantu team untuk:
✅ Track semua changes di database  
✅ Audit trail untuk compliance  
✅ Debugging dan troubleshooting  
✅ Understanding user behavior  

Implementasi sudah production-ready dan bisa digunakan langsung untuk track all important activities dalam aplikasi.

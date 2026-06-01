# Activity Log System Documentation

## 📋 Daftar Isi
1. [Apa itu Activity Log?](#apa-itu-activity-log)
2. [Arsitektur & Teknologi](#arsitektur--teknologi)
3. [Struktur File & Folder](#struktur-file--folder)
4. [Database Schema](#database-schema)
5. [Implementasi](#implementasi)
6. [Cara Menggunakan](#cara-menggunakan)
7. [Best Practices](#best-practices)
8. [Troubleshooting](#troubleshooting)

---

## Apa itu Activity Log?

**Activity Log** adalah sistem pencatatan otomatis yang melacak setiap perubahan data di aplikasi. Sistem ini membantu:

- ✅ **Audit Trail** — mencatat siapa, kapan, dan apa yang diubah
- ✅ **Compliance** — memenuhi kebutuhan audit dan compliance regulasi
- ✅ **Debugging** — melacak masalah atau perubahan tidak terduga
- ✅ **User Accountability** — melacak tindakan user untuk tanggung jawab
- ✅ **Data Recovery** — melihat history perubahan untuk recovery

### Contoh Skenario

```
User Admin login → Log entry: "Admin [IP: 192.168.1.1] logged in"
User Admin mengubah role User → Log entry: "User #5 role changed from 'staff' to 'admin'"
User Admin delete Company → Log entry: "Company #3 deleted by Admin"
```

---

## Arsitektur & Teknologi

### Tech Stack
- **Package**: [Spatie Laravel Activity Log](https://github.com/spatie/laravel-activitylog)
- **Database**: SQLite (default) atau database lain
- **UI Framework**: Livewire v4 + Flux UI v2
- **Authentication**: Laravel Fortify

### Komponen Utama

```
Activity Log System
├── Database Layer (activity_log table)
├── Model Layer (Spatie's Activity model + custom ActivityLog model)
├── Middleware & Event Listeners (AppServiceProvider)
├── UI Layer (Livewire Volt page)
└── API Layer (untuk filter, search, export)
```

---

## Struktur File & Folder

```
laravel-livewire-starter/
│
├── app/
│   ├── Models/
│   │   └── ActivityLog.php                 ← Custom ActivityLog model
│   │
│   └── Providers/
│       └── AppServiceProvider.php          ← Activity logging setup
│
├── database/
│   └── migrations/
│       └── 2026_05_26_000000_create_activity_log_table.php
│
├── resources/
│   └── views/
│       └── pages/
│           └── activity-logs/
│               └── ⚡index.blade.php       ← Volt page (UI)
│
├── routes/
│   └── web.php                            ← Route definition
│
├── config/
│   └── activitylog.php                    ← Spatie config (auto-generated)
│
└── docs/
    └── guides/
        └── ACTIVITY_LOG_SYSTEM.md         ← Dokumentasi ini
```

### Penjelasan File Penting

| File | Deskripsi |
|------|-----------|
| `app/Models/ActivityLog.php` | Extended Spatie Activity model dengan helper methods (`getChangesAttribute()`, `subjectLabel()`) |
| `app/Providers/AppServiceProvider.php` | Mengatur logging untuk auth events (login, logout, failed login) |
| `database/migrations/...create_activity_log_table.php` | Schema database untuk activity log |
| `resources/views/pages/activity-logs/⚡index.blade.php` | UI Livewire untuk menampilkan activity logs dengan filter & search |
| `config/activitylog.php` | Konfigurasi Spatie activity log (dibuat otomatis saat install) |

---

## Database Schema

### Tabel: `activity_log`

```sql
CREATE TABLE activity_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    log_name VARCHAR(255) NULLABLE INDEX,        -- 'auth', 'model', custom names
    description TEXT NOT NULL,                    -- "User created", "Role updated", etc.
    subject_type VARCHAR(255) NULLABLE,           -- Polymorphic: App\Models\User, App\Models\Company
    subject_id BIGINT NULLABLE,                   -- ID dari subject yang berubah
    event VARCHAR(255) NULLABLE,                  -- 'created', 'updated', 'deleted'
    causer_type VARCHAR(255) NULLABLE,            -- Polymorphic: Who made the change
    causer_id BIGINT NULLABLE,                    -- User ID who made the change
    attribute_changes JSON NULLABLE,              -- Spatie internal changes tracking
    properties JSON NULLABLE,                     -- Custom meta data: { ip, user_agent, old: {...}, attributes: {...} }
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

### Contoh Data

```json
{
  "id": 1,
  "log_name": "auth",
  "description": "Admin logged in",
  "subject_type": null,
  "subject_id": null,
  "event": null,
  "causer_type": "App\\Models\\User",
  "causer_id": 1,
  "properties": {
    "ip": "192.168.1.100",
    "user_agent": "Mozilla/5.0..."
  }
}

{
  "id": 2,
  "log_name": "model",
  "description": "User updated",
  "subject_type": "App\\Models\\User",
  "subject_id": 5,
  "event": "updated",
  "causer_type": "App\\Models\\User",
  "causer_id": 1,
  "properties": {
    "old": { "name": "John", "email": "john@old.com" },
    "attributes": { "name": "John Doe", "email": "john@new.com" }
  }
}
```

---

## Implementasi

### 1. Installation & Setup (Sudah Selesai)

Package sudah ter-install. Untuk setup baru:

```bash
# Install package
composer require spatie/laravel-activitylog

# Publish config & migration
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
php artisan migrate
```

### 2. Model Configuration

#### Basic - Setup Automatic Logging pada Model

Di `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class User extends Model
{
    use LogsActivity;  // ← Add this trait

    // Define what to log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()                    // Log hanya field yang berubah
            ->dontLogEmptyChanges()             // Jangan log jika tidak ada perubahan
            ->useLogName('model')               // Log name: 'model'
            ->setDescriptionForEvent(fn (string $e) => "User {$e}");  // Description
    }
}
```

#### Advanced - Custom Log Name & Ignored Fields

```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnlyDirty()
        ->dontLogEmptyChanges()
        ->useLogName('user_management')
        ->setDescriptionForEvent(fn (string $e) => "User {$e}")
        ->logOnly(['name', 'email', 'role'])  // Log only specific columns
        ->logExcept(['password', 'remember_token'])  // Exclude sensitive columns
        ->setDescriptionForEvent(
            fn (string $event) => match ($event) {
                'created' => 'User baru dibuat',
                'updated' => 'User data diubah',
                'deleted' => 'User dihapus',
                default => "User {$event}"
            }
        );
}
```

### 3. Manual Activity Logging (di AppServiceProvider atau Controller)

```php
// Contoh di AppServiceProvider.php
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

Event::listen(Login::class, function (Login $event) {
    activity('auth')                           // Log name: 'auth'
        ->causedBy($event->user)               // Siapa yang melakukan
        ->withProperties([                      // Metadata tambahan
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ])
        ->log('login');                         // Description: 'login'
});

Event::listen(Logout::class, function (Logout $event) {
    activity('auth')
        ->causedBy($event->user)
        ->withProperties(['ip' => request()->ip()])
        ->log('logout');
});
```

Atau di Controller/Service:

```php
// Contoh manual logging di controller
activity('inventory')
    ->causedBy(auth()->user())
    ->on($product)  // Set subject
    ->withProperties(['quantity' => 100, 'reason' => 'Restock'])
    ->log('stock_updated');
```

### 4. ActivityLog Custom Model

File: `app/Models/ActivityLog.php`

```php
<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as BaseActivity;

class ActivityLog extends BaseActivity
{
    /**
     * Helper: Get array of changes (old vs new)
     * 
     * Returns: [
     *   ['field' => 'name', 'before' => 'John', 'after' => 'Jane'],
     *   ['field' => 'email', 'before' => 'john@old.com', 'after' => 'jane@new.com']
     * ]
     */
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
            $after = $new[$key] ?? null;

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

    /**
     * Helper: Get subject type name
     * 
     * Returns: 'User', 'Company', 'Branch', etc.
     */
    public function subjectLabel(): string
    {
        if (!$this->subject_type) {
            return '—';
        }
        return class_basename($this->subject_type);
    }
}
```

### 5. Configuration File

File: `config/activitylog.php` (auto-generated)

```php
return [
    // Default log name ketika tidak specified
    'default' => env('ACTIVITY_LOGGER_DEFAULT', 'default'),

    // Database table name
    'table_name' => env('ACTIVITY_LOG_TABLE_NAME', 'activity_log'),

    // Model untuk activity log
    'activity_model' => App\Models\ActivityLog::class,

    // Database connection
    'database_connection' => env('ACTIVITY_LOG_DATABASE_CONNECTION', null),
];
```

---

## Cara Menggunakan

### 1. View Activity Logs (UI)

Akses URL: `http://localhost:8000/activity-logs`

**Fitur:**
- 🔍 **Search** — cari by description atau event
- 🏷️ **Filter by Log Type** — Auth / Model
- 📦 **Filter by Subject** — User / Company / Branch / Role / Permission
- 👤 **Filter by Causer** — Siapa yang melakukan perubahan
- 📅 **Filter by Date Range** — Dari dan sampai tanggal
- 📊 **Sort** — Ascending/Descending by kolom apapun
- 🔎 **View Detail** — Modal yang menampilkan detail perubahan

### 2. Query Activity Logs Programmatically

```php
use App\Models\ActivityLog;

// Get all logs
$logs = ActivityLog::all();

// Filter by log name
$authLogs = ActivityLog::where('log_name', 'auth')->get();

// Filter by subject
$userLogs = ActivityLog::where('subject_type', User::class)
    ->where('subject_id', 5)
    ->get();

// Filter by causer (who did it)
$adminLogs = ActivityLog::where('causer_type', User::class)
    ->where('causer_id', auth()->id())
    ->get();

// Get changes for a specific log
$log = ActivityLog::find(1);
$changes = $log->changes;  // Returns array of field changes
// [
//   ['field' => 'name', 'before' => 'John', 'after' => 'Jane'],
//   ['field' => 'email', 'before' => 'john@old.com', 'after' => 'jane@new.com']
// ]

// Get all properties
$metadata = $log->properties->toArray();
// ['ip' => '192.168.1.100', 'user_agent' => '...', 'old' => [...], 'attributes' => [...]]

// Ordered by latest
$recentLogs = ActivityLog::latest()->limit(10)->get();
```

### 3. Custom Logging

Di mana saja dalam aplikasi (Controller, Service, Model):

```php
use Illuminate\Support\Facades\DB;

// Dalam Controller atau Seeder
activity()
    ->useLog('payment')                      // Log name
    ->causedBy(auth()->user())               // User yang melakukan aksi
    ->on(Order::find(1))                     // Subject yang berubah
    ->withProperties([                        // Metadata
        'order_id' => 1,
        'amount' => 100000,
        'payment_method' => 'transfer',
        'status' => 'pending'
    ])
    ->log('payment_initiated');              // Event/description
```

---

## Best Practices

### ✅ DO

1. **Log Sensitive Events**
   ```php
   // DO: Log authentication attempts
   activity('auth')->causedBy($user)->log('login');
   
   // DO: Log role/permission changes
   activity('authorization')->causedBy($user)
       ->on($targetUser)
       ->log('role_assigned');
   ```

2. **Use Descriptive Log Names**
   ```php
   // Good
   activity('user_management')->log('user_created');
   activity('payment')->log('payment_processed');
   activity('auth')->log('login');
   
   // Bad
   activity('log')->log('action');
   ```

3. **Include Relevant Metadata**
   ```php
   // Good: Include context
   activity('invoice')
       ->causedBy($user)
       ->withProperties([
           'invoice_id' => $invoice->id,
           'amount' => $invoice->total,
           'reason' => 'Late payment'
       ])
       ->log('payment_received');
   
   // Bad: No context
   activity('invoice')->log('payment_received');
   ```

4. **Exclude Sensitive Data**
   ```php
   // Good: Exclude passwords and tokens
   public function getActivitylogOptions(): LogOptions
   {
       return LogOptions::defaults()
           ->logExcept(['password', 'api_token', 'remember_token'])
           ->logOnly(['email', 'name', 'phone']);
   }
   
   // Bad: Logging passwords
   LogOptions::defaults()->logOnly(['password', 'email']);
   ```

### ❌ DON'T

1. **Don't Log Every Single Query**
   ```php
   // Bad: Too much noise
   LogsActivity::logOnlyDirty() false;  // Log even no changes
   ```

2. **Don't Log Large Binary Data**
   ```php
   // Bad: Storing images in activity log
   activity()->withProperties(['image_data' => $binaryImageData]);
   
   // Good: Store reference instead
   activity()->withProperties(['image_path' => $imagePath]);
   ```

3. **Don't Expose Passwords or API Keys**
   ```php
   // Bad
   activity('auth')->withProperties(['password' => $plainPassword]);
   
   // Good
   activity('auth')->withProperties(['password_changed' => true]);
   ```

4. **Don't Use Generic Descriptions**
   ```php
   // Bad
   activity()->log('updated');
   activity()->log('changed');
   
   // Good
   activity('user')->log('profile_updated');
   activity('settings')->log('notification_preferences_changed');
   ```

---

## Troubleshooting

### Q1: Activity log tidak tercatat

**Solusi:**
1. Pastikan migration sudah dijalankan: `php artisan migrate`
2. Check model menggunakan trait `LogsActivity`
3. Pastikan `useLogName()` di-set di `getActivitylogOptions()`
4. Verify database connection aktif

```php
// Debug: Check if logging is working
$user = User::find(1);
$user->name = 'New Name';
$user->save();

// Check if logged
$logs = ActivityLog::where('subject_type', User::class)
    ->where('subject_id', 1)
    ->get();
dd($logs);  // Should show the log entry
```

### Q2: Activity log terlalu banyak (performance issue)

**Solusi:**
1. Gunakan `logOnlyDirty()` — log hanya field yang berubah
2. Gunakan `logExcept()` — exclude field yang tidak perlu
3. Implement **log rotation/archival** untuk old logs

```php
// Good: Log hanya yang perlu
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnlyDirty()
        ->dontLogEmptyChanges()
        ->logOnly(['name', 'email', 'role'])  // Hanya field penting
        ->logExcept(['updated_at']);
}
```

### Q3: Ingin mengexport/report activity logs

**Solusi:**
Di Volt page, tambahkan export button (sudah ada permission check):

```blade
@can('activity_logs.export')
    <flux:button variant="ghost" icon="arrow-down-tray" wire:click="exportCsv">
        Export
    </flux:button>
@endcan
```

Implementasi method di component:
```php
public function exportCsv()
{
    // Generate CSV dari filtered logs
    // Download atau email
}
```

### Q4: Ingin filter logs dengan permission

**Solusi:**
Di activity-logs page, permission check sudah ada:

```php
@can('activity_logs.view')
    {{-- Show activity logs page --}}
@endcan

@can('activity_logs.export')
    {{-- Show export button --}}
@endcan
```

Tambah permission ke `RolesPermissionsSeeder`:
```php
Permission::create(['name' => 'activity_logs.view', 'guard_name' => 'web']);
Permission::create(['name' => 'activity_logs.export', 'guard_name' => 'web']);

// Assign to roles
Role::where('name', 'superadmin')->first()
    ->givePermissionTo(['activity_logs.view', 'activity_logs.export']);
```

---

## Query Examples

### Get Login Activities Last 7 Days

```php
$recentLogins = ActivityLog::where('log_name', 'auth')
    ->where('description', 'like', '%login%')
    ->where('created_at', '>=', now()->subDays(7))
    ->with('causer')
    ->latest()
    ->get();

foreach ($recentLogins as $log) {
    echo $log->causer->name . " logged in at " . $log->created_at;
}
```

### Get All Changes by Specific User

```php
$userChanges = ActivityLog::where('causer_type', User::class)
    ->where('causer_id', auth()->id())
    ->where('log_name', 'model')
    ->latest()
    ->get();

foreach ($userChanges as $log) {
    echo "Changed {$log->subjectLabel()} #{$log->subject_id}";
    foreach ($log->changes as $change) {
        echo "  {$change['field']}: {$change['before']} → {$change['after']}";
    }
}
```

### Get All Deleted Records

```php
$deletedRecords = ActivityLog::where('event', 'deleted')
    ->with('causer')
    ->latest()
    ->get();

foreach ($deletedRecords as $log) {
    echo "{$log->subjectLabel()} #{$log->subject_id} was deleted by {$log->causer->name}";
}
```

---

## Kesimpulan

Activity Log System adalah fitur penting untuk:
- 📝 Audit & compliance
- 🔍 Debugging & troubleshooting
- 👥 User accountability
- 📊 Analytics & reporting

Dengan mengikuti best practices dan struktur yang sudah ada, team dapat dengan mudah melacak semua perubahan di aplikasi.

**Next Steps:**
- Implementasikan pada model baru dengan `LogsActivity` trait
- Setup email/webhook notifications untuk critical logs
- Create reports/dashboards dari activity log data

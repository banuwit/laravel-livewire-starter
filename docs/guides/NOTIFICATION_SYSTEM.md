# Notification System dengan Laravel Reverb - Documentation

## 📋 Daftar Isi
1. [Apa itu Notification System dengan Reverb?](#apa-itu-notification-system-dengan-reverb)
2. [Arsitektur & Teknologi](#arsitektur--teknologi)
3. [Struktur File & Folder](#struktur-file--folder)
4. [Implementasi Step-by-Step](#implementasi-step-by-step)
5. [Cara Menggunakan](#cara-menggunakan)
6. [Database Schema](#database-schema)
7. [Broadcasting Architecture](#broadcasting-architecture)
8. [Best Practices](#best-practices)
9. [Troubleshooting](#troubleshooting)
10. [Advanced Topics](#advanced-topics)

---

## Apa itu Notification System dengan Reverb?

**Notification System** adalah sistem untuk mengirim pesan/notifikasi kepada user. **Laravel Reverb** adalah WebSocket server yang memungkinkan notifikasi dikirim **real-time** tanpa user perlu refresh page.

### Keunggulan

✅ **Real-time** — Notifikasi langsung terima tanpa refresh
✅ **Persistent** — Disimpan di database untuk history
✅ **Multi-channel** — Database, Email, Broadcast, Custom channels
✅ **SPA-friendly** — Bekerja sempurna dengan wire:navigate (Livewire)
✅ **Zero-config** — Laravel Reverb plug & play, tidak perlu external services

### Contoh Use Cases

```
1. User Login Alert
   User A login → Real-time notification ke User A's semua devices

2. Role Assignment
   Admin assign role to User B → Notification ke User B: "Role berubah menjadi admin"

3. System Announcement
   Admin post announcement → Broadcast ke semua user secara real-time

4. Order Status
   Order payment received → Notification ke customer: "Pembayaran diterima"

5. Data Update
   Company profile updated → Notification ke semua members
```

---

## Arsitektur & Teknologi

### Tech Stack

| Component | Technology |
|-----------|------------|
| **WebSocket Server** | Laravel Reverb |
| **Frontend WebSocket** | Laravel Echo + Pusher JS |
| **UI Framework** | Livewire v4 + Flux UI v2 |
| **Database** | SQLite / PostgreSQL / MySQL |
| **Queue** | Database queue (built-in Laravel) |
| **Framework** | Laravel 13 |

### Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        Application Layer                         │
├─────────────────────────────────────────────────────────────────┤
│  NotificationBell Component (Livewire) ← Echo Listener          │
│  Notifications Page (Volt)                                       │
└─────────────────────────────────────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│                    Broadcasting Layer                            │
├─────────────────────────────────────────────────────────────────┤
│  Laravel Echo (Browser)  ←→  Laravel Reverb (WebSocket Server)  │
│  - Private Channels      ←→  - Authenticates connections        │
│  - Event Listeners       ←→  - Broadcasts events                │
└─────────────────────────────────────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│                  Notification Backend                            │
├─────────────────────────────────────────────────────────────────┤
│  Notification Classes  →  Queue Worker  →  Broadcasting Events  │
│  (UserLoginNotification)  (async)          (to WebSocket)       │
└─────────────────────────────────────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│                  Persistence Layer                               │
├─────────────────────────────────────────────────────────────────┤
│  notifications table (Database) - Stores notification history    │
└─────────────────────────────────────────────────────────────────┘
```

### Flow Diagram: Sending Notification

```
Event Triggered (e.g., User Login)
    ↓
Event Listener (AppServiceProvider)
    ↓
Call $user->notify(new UserLoginNotification(...))
    ↓
Notification queued to database queue
    ↓
Queue Worker processes notification
    ↓
Notification channels triggered:
  ├→ 'database' channel → Save to notifications table
  └→ 'broadcast' channel → Send to Reverb WebSocket
    ↓
Reverb broadcasts to private channel: "App.Models.User.{userId}"
    ↓
Laravel Echo (Browser) receives event
    ↓
Livewire listener (#[On('echo-private:...')]) triggered
    ↓
Component re-renders (badge count update)
    ↓
Toast notification pops up
    ↓
Dropdown shows latest notifications
```

---

## Struktur File & Folder

```
laravel-livewire-starter/
│
├── app/
│   ├── Notifications/
│   │   └── UserLoginNotification.php         ← Notification class
│   │
│   ├── Livewire/
│   │   └── NotificationBell.php              ← Livewire component (real-time)
│   │
│   └── Providers/
│       └── AppServiceProvider.php            ← Event listener setup
│
├── database/
│   └── migrations/
│       ├── 2026_05_29_153515_create_notifications_table.php
│       └── (existing tables)
│
├── resources/
│   ├── views/
│   │   ├── components/
│   │   │   └── notification-bell.blade.php  ← Blade wrapper component
│   │   │
│   │   ├── livewire/
│   │   │   └── notification-bell.blade.php  ← Bell UI view
│   │   │
│   │   ├── layouts/
│   │   │   ├── app/
│   │   │   │   ├── sidebar.blade.php        ← Insert bell component
│   │   │   │   └── header.blade.php         ← Insert bell component
│   │   │   └── app.blade.php
│   │   │
│   │   └── pages/
│   │       └── notifications/
│   │           └── ⚡index.blade.php        ← Full notifications page
│   │
│   └── js/
│       └── app.js                           ← Echo initialization
│
├── routes/
│   ├── web.php                              ← Route definition
│   └── channels.php                         ← Broadcast channel auth
│
├── config/
│   ├── broadcasting.php                     ← Broadcasting config
│   ├── reverb.php                           ← Reverb server config
│   └── app.php
│
├── bootstrap/
│   └── app.php                              ← Register channels.php
│
├── composer.json                            ← Dev script with reverb:start
└── docs/
    └── guides/
        └── NOTIFICATION_SYSTEM.md           ← Dokumentasi ini
```

### Penjelasan File Penting

| File | Deskripsi |
|------|-----------|
| `app/Notifications/UserLoginNotification.php` | Notification class dengan `via()`, `toDatabase()`, `toBroadcast()` methods |
| `app/Livewire/NotificationBell.php` | Livewire component dengan Echo listener untuk real-time updates |
| `resources/views/livewire/notification-bell.blade.php` | UI bell icon + dropdown dengan Alpine.js interactivity |
| `resources/views/components/notification-bell.blade.php` | Blade wrapper untuk easy inclusion di layout |
| `resources/views/pages/notifications/⚡index.blade.php` | Volt page untuk full notifications list dengan filter & pagination |
| `resources/js/app.js` | Initialize Laravel Echo dengan Reverb WebSocket |
| `routes/channels.php` | Authorize private broadcast channels per user |
| `config/reverb.php` | Konfigurasi Reverb server (host, port, protocol) |
| `database/migrations/.../create_notifications_table.php` | Schema untuk notifications table (uuid, type, data, read_at) |

---

## Implementasi Step-by-Step

### Phase 1: Install & Configure Infrastructure

#### Step 1: Install Packages

```bash
# Install Reverb (WebSocket server)
composer require laravel/reverb

# Install Frontend packages
npm install --save-dev laravel-echo pusher-js

# Run Reverb installer (auto-publishes config & generates keys)
php artisan reverb:install
```

#### Step 2: Update .env

```env
# Broadcast driver
BROADCAST_CONNECTION=reverb

# Reverb configuration (auto-generated by reverb:install)
REVERB_APP_ID=473110
REVERB_APP_KEY=756eo20ltalbf9bzunu2
REVERB_APP_SECRET=gva6g4jwkwkpiwgnxwh8
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# For Vite frontend
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

#### Step 3: Register Channels Route

File: `bootstrap/app.php`

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',  // ← ADD THIS
        health: '/up',
    )
    // ... rest of config
```

### Phase 2: Database

#### Step 4: Create Notifications Table

```bash
php artisan notifications:table
php artisan migrate
```

**Schema** (auto-generated):
```php
Schema::create('notifications', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('type');                   // Notification class FQCN
    $table->morphs('notifiable');             // Who gets the notification
    $table->text('data');                     // JSON payload
    $table->timestamp('read_at')->nullable(); // When marked as read
    $table->timestamps();
});
```

### Phase 3: Backend - Notification Class

#### Step 5: Create Notification Class

File: `app/Notifications/UserLoginNotification.php`

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class UserLoginNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $ipAddress,
        public readonly string $userAgent,
    ) {}

    // Define which channels to use
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];  // Store + Broadcast
    }

    // Store to notifications table
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Login Detected',
            'message' => "Login from {$this->ipAddress}",
            'icon' => 'arrow-right-end-on-rectangle',
            'color' => 'blue',
            'url' => '/activity-logs',
        ];
    }

    // Send via WebSocket
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Login Detected',
            'message' => "Login from {$this->ipAddress}",
            'icon' => 'arrow-right-end-on-rectangle',
            'color' => 'blue',
            'url' => '/activity-logs',
        ]);
    }

    // WebSocket event name
    public function broadcastType(): string
    {
        return 'NotificationSent';
    }
}
```

### Phase 4: Backend - Event Listener

#### Step 6: Wire Notification to Event

File: `app/Providers/AppServiceProvider.php`

```php
use Illuminate\Auth\Events\Login;
use App\Notifications\UserLoginNotification;

public function boot(): void
{
    Event::listen(Login::class, function (Login $event) {
        // Existing activity log...
        activity('auth')
            ->causedBy($event->user)
            ->withProperties(['ip' => request()->ip(), 'user_agent' => request()->userAgent()])
            ->log('login');

        // Send notification (async via queue)
        $event->user->notify(new UserLoginNotification(
            ipAddress: request()->ip(),
            userAgent: request()->userAgent(),
        ));
    });
}
```

### Phase 5: Broadcasting Setup

#### Step 7: Configure Broadcast Channels

File: `routes/channels.php`

```php
<?php

use Illuminate\Support\Facades\Broadcast;

// Private channel - only user can subscribe
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

### Phase 6: Frontend - JavaScript Setup

#### Step 8: Initialize Laravel Echo

File: `resources/js/app.js`

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? 'localhost',
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        },
    },
});
```

### Phase 7: Frontend - Livewire Component

#### Step 9: Create NotificationBell Livewire Component

File: `app/Livewire/NotificationBell.php`

```php
<?php

namespace App\Livewire;

use Illuminate\Notifications\DatabaseNotification;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;
    public array $recentNotifications = [];
    public bool $dropdownOpen = false;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    protected function loadNotifications(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $this->unreadCount = $user->unreadNotifications()->count();
        $this->recentNotifications = $user->notifications()
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? 'Notification',
                'message' => $n->data['message'] ?? '',
                'icon' => $n->data['icon'] ?? 'bell',
                'color' => $n->data['color'] ?? 'zinc',
                'url' => $n->data['url'] ?? null,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    // Listen to WebSocket event
    #[On('echo-private:App.Models.User.{userId},NotificationSent')]
    public function onNotificationReceived(array $event): void
    {
        $this->loadNotifications();
        $this->dispatch('show-notification-toast',
            title: $event['title'] ?? 'New notification',
            message: $event['message'] ?? '',
        );
    }

    public function markAsRead(string $id): void
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first()
            ?->markAsRead();

        $this->loadNotifications();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function getUserIdProperty(): int|string
    {
        return auth()->id();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
```

#### Step 10: Create NotificationBell View

File: `resources/views/livewire/notification-bell.blade.php`

```blade
<div x-data="{ open: false }"
     x-on:show-notification-toast.window="
         $flux.toast({
             variant: 'default',
             heading: $event.detail.title,
             text: $event.detail.message
         })
     ">

    {{-- Bell button with badge --}}
    <div class="relative">
        <flux:button
            variant="subtle"
            square
            size="sm"
            x-on:click="open = !open"
            aria-label="Notifications"
        >
            <flux:icon.bell :variant="$unreadCount > 0 ? 'solid' : 'outline'" class="size-5" />
        </flux:button>

        @if($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white leading-none ring-2 ring-white dark:ring-zinc-900">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </div>

    {{-- Dropdown panel --}}
    <div x-show="open"
         x-on:click.outside="open = false"
         x-transition
         class="absolute right-0 top-full mt-2 w-80 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 shadow-lg z-50 overflow-hidden"
         wire:ignore.self>

        <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-100 dark:border-zinc-800">
            <flux:heading size="sm">Notifications</flux:heading>
            @if($unreadCount > 0)
                <flux:button variant="ghost" size="xs" wire:click="markAllAsRead">
                    Mark all read
                </flux:button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse($recentNotifications as $notif)
                <div wire:key="notif-{{ $notif['id'] }}"
                     class="flex gap-3 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors {{ is_null($notif['read_at']) ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}">
                    <div class="flex-1 min-w-0">
                        <flux:text size="sm" variant="{{ is_null($notif['read_at']) ? 'strong' : 'default' }}" class="truncate">
                            {{ $notif['title'] }}
                        </flux:text>
                        <flux:text size="xs" variant="muted" class="mt-0.5">
                            {{ $notif['message'] }}
                        </flux:text>
                        <flux:text size="xs" variant="muted" class="mt-1">
                            {{ $notif['created_at'] }}
                        </flux:text>
                    </div>
                    @if(is_null($notif['read_at']))
                        <button wire:click="markAsRead('{{ $notif['id'] }}')"
                                class="shrink-0 text-zinc-400 hover:text-blue-500 transition-colors"
                                title="Mark as read">
                            <flux:icon.check-circle variant="micro" />
                        </button>
                    @endif
                </div>
            @empty
                <div class="py-10 text-center">
                    <flux:icon.bell-slash class="size-8 mx-auto opacity-30 mb-2" />
                    <flux:text size="sm" variant="muted">No notifications yet</flux:text>
                </div>
            @endforelse
        </div>

        <div class="px-4 py-2 border-t border-zinc-100 dark:border-zinc-800">
            <flux:link href="{{ route('notifications.index') }}" wire:navigate class="text-sm text-blue-600 dark:text-blue-400">
                View all notifications
            </flux:link>
        </div>
    </div>
</div>
```

#### Step 11: Create Blade Component Wrapper

File: `resources/views/components/notification-bell.blade.php`

```blade
@auth
    <livewire:notification-bell />
@endauth
```

#### Step 12: Insert Bell in Layouts

File: `resources/views/layouts/app/sidebar.blade.php`

```blade
<!-- Desktop header -->
<flux:header sticky>
    <!-- ... other items ... -->
    <flux:spacer />
    <x-theme-toggle class="hidden lg:block mr-2" />
    <x-notification-bell class="hidden lg:block mr-2" />  {{-- ADD THIS --}}
    <x-desktop-user-menu class="hidden lg:block" />
</flux:header>

<!-- Mobile header -->
<flux:header class="lg:hidden">
    <flux:sidebar.collapse />
    <flux:spacer />
    <x-theme-toggle class="mr-2" />
    <x-notification-bell class="mr-2" />  {{-- ADD THIS --}}
    <x-desktop-user-menu />
</flux:header>
```

### Phase 8: Full Notifications Page

#### Step 13: Create Notifications Volt Page

File: `resources/views/pages/notifications/⚡index.blade.php`

```php
<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Notifications')] class extends Component {
    use WithPagination;

    public string $filter = 'all'; // 'all' | 'unread' | 'read'

    public function markAsRead(string $id): void
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first()
            ?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function deleteNotification(string $id): void
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first()
            ?->delete();
    }

    public function render()
    {
        $query = auth()->user()->notifications();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return view('pages.notifications.index', [
            'notifications' => $query->latest()->paginate(20),
            'unreadCount' => auth()->user()->unreadNotifications()->count(),
        ]);
    }
};
?>

<!-- View content here -->
```

#### Step 14: Add Notifications Route

File: `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // ... other routes ...
    Route::livewire('notifications', 'pages::notifications.index')->name('notifications.index');
});
```

### Phase 9: Dev Workflow

#### Step 15: Update Dev Script

File: `composer.json`

```json
{
    "scripts": {
        "dev": [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fdba74,#fb923c\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"npm run dev\" \"php artisan reverb:start --debug\" --names='server,queue,vite,reverb'"
        ]
    }
}
```

#### Step 16: Build & Start

```bash
# Build frontend
npm run build

# Start dev server (4 processes)
composer dev
```

---

## Cara Menggunakan

### 1. Mengirim Notification dari Code

#### Example 1: Dari Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\UserLoginNotification;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // ... auth logic ...

        $user->notify(new UserLoginNotification(
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        ));

        return redirect()->route('dashboard');
    }
}
```

#### Example 2: Dari Service Class

```php
<?php

namespace App\Services;

use App\Notifications\RoleAssignedNotification;

class UserService
{
    public function assignRole(User $user, Role $role)
    {
        // Assign role...
        $user->assignRole($role);

        // Send notification
        $user->notify(new RoleAssignedNotification($role));
    }
}
```

#### Example 3: Broadcast to Multiple Users

```php
// Notify all admin users
$adminUsers = User::role('admin')->get();

foreach ($adminUsers as $user) {
    $user->notify(new PaymentReceivedNotification($order));
}

// Or using collection
User::role('admin')->each(fn ($user) => 
    $user->notify(new PaymentReceivedNotification($order))
);
```

### 2. View Notifications

#### Via UI

Access: `http://localhost:8000/notifications`

**Features:**
- View all notifications
- Filter by: All / Unread / Read
- Pagination (20 per page)
- Mark as read / Delete
- Search & sort

#### Via Livewire Component

The bell icon automatically shows:
- Unread count badge
- Dropdown with 8 recent notifications
- Real-time updates via WebSocket

### 3. Query Notifications Programmatically

```php
use Illuminate\Notifications\DatabaseNotification;

// Get user's unread notifications
$unread = auth()->user()->unreadNotifications;

// Get all notifications
$all = auth()->user()->notifications;

// Query specific notifications
$recent = DatabaseNotification::where('notifiable_type', User::class)
    ->where('notifiable_id', $userId)
    ->whereNull('read_at')
    ->latest()
    ->get();

// Access notification data
foreach ($recent as $notification) {
    echo $notification->data['title'];
    echo $notification->data['message'];
}

// Mark as read
$notification->markAsRead();

// Mark all as read
auth()->user()->unreadNotifications->markAsRead();
```

---

## Database Schema

### Table: `notifications`

```sql
CREATE TABLE notifications (
    id CHAR(36) PRIMARY KEY,                  -- UUID
    type VARCHAR(255) NOT NULL,               -- e.g. App\Notifications\UserLoginNotification
    notifiable_type VARCHAR(255) NOT NULL,    -- Polymorphic: App\Models\User
    notifiable_id BIGINT UNSIGNED NOT NULL,   -- User ID
    data JSON NOT NULL,                       -- Notification payload
    read_at TIMESTAMP NULL,                   -- When marked as read (null = unread)
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    KEY idx_notifiable (notifiable_type, notifiable_id)
);
```

### Example Data

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "type": "App\\Notifications\\UserLoginNotification",
  "notifiable_type": "App\\Models\\User",
  "notifiable_id": 1,
  "data": {
    "title": "New Login Detected",
    "message": "Login from 192.168.1.100",
    "icon": "arrow-right-end-on-rectangle",
    "color": "blue",
    "url": "/activity-logs"
  },
  "read_at": null,
  "created_at": "2026-05-29 22:45:00",
  "updated_at": "2026-05-29 22:45:00"
}
```

---

## Broadcasting Architecture

### How Reverb Works

1. **Client Connect**
   ```
   Browser → WebSocket Connection → Reverb Server
   ```

2. **Subscribe to Channel**
   ```
   Browser: Echo.private('App.Models.User.1')
       ↓
   Sends auth request to: /broadcasting/auth
       ↓
   Server authorizes: Is user 1 === auth user?
       ↓
   Subscribe successful → Listen for events
   ```

3. **Send Notification**
   ```
   Backend:  $user->notify(new Notification())
       ↓
   Queue Worker processes notification
       ↓
   Broadcasting channel triggered
       ↓
   Event: "NotificationSent" 
       ↓
   Sent to: "App.Models.User.1" 
       ↓
   All connections on that channel receive event
   ```

4. **Receive & Handle**
   ```
   Browser receives event via WebSocket
       ↓
   Livewire Echo listener triggered: #[On('echo-private:...')]
       ↓
   Component method executed: onNotificationReceived()
       ↓
   Component re-renders (update badge)
       ↓
   Toast notification displayed
   ```

### Channel Authorization

File: `routes/channels.php`

```php
// Private channel - only owner can access
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;  // true = authorized, false = denied
});

// Presence channel - show who's online
Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    if ($user->can('chat', $roomId)) {
        return ['id' => $user->id, 'name' => $user->name];  // Send user info
    }
});
```

---

## Best Practices

### ✅ DO

1. **Send Async Notifications**
   ```php
   // Good: Implement ShouldQueue
   class NotifyUserNotification extends Notification implements ShouldQueue
   {
       // ...
   }

   // Bad: Synchronous (blocks request)
   class NotifyUserNotification extends Notification
   {
       // Missing ShouldQueue
   }
   ```

2. **Use Descriptive Notification Data**
   ```php
   // Good: Clear title & message
   public function toDatabase(): array {
       return [
           'title' => 'Payment Received',
           'message' => "Order #123 payment of $100 received",
           'icon' => 'check-circle',
           'color' => 'green',
           'url' => '/orders/123',
       ];
   }

   // Bad: Vague data
   public function toDatabase(): array {
       return ['status' => 'ok'];
   }
   ```

3. **Include Action URLs**
   ```php
   // Good: Clickable notification links
   'url' => route('orders.show', $order),

   // Better: Include all context needed
   'data' => [
       'order_id' => $order->id,
       'url' => route('orders.show', $order),
   ]
   ```

4. **Use Multiple Channels When Needed**
   ```php
   // Good: Notify via multiple channels
   public function via(): array {
       return ['database', 'broadcast', 'mail'];  // DB + Real-time + Email
   }

   // Context-specific:
   // - database: Always store for history
   // - broadcast: For real-time UX
   // - mail: For critical notifications
   ```

5. **Handle SPA Navigation Properly**
   ```php
   // Good: WebSocket survives wire:navigate
   // Echo is initialized once, persists across SPA transitions
   // Livewire listeners still work

   // Configuration in app.js already handles this
   window.Echo = new Echo({ ... });  // Persists
   ```

### ❌ DON'T

1. **Don't Send Sensitive Data**
   ```php
   // Bad: Exposing sensitive info
   'data' => [
       'credit_card' => '4111-1111-1111-1111',
       'password' => $user->password,
   ]

   // Good: Send only necessary info
   'data' => [
       'account_id' => $user->account_id,
       'message' => 'Account updated',
   ]
   ```

2. **Don't Notify Without Async**
   ```php
   // Bad: Blocking the request
   $user->notify(new SlowNotification());  // If not ShouldQueue

   // Good: Queue it
   class SlowNotification extends Notification implements ShouldQueue { }
   ```

3. **Don't Broadcast to Unauthorized Users**
   ```php
   // Bad: No authorization
   Broadcast::channel('sensitive-data', function () {
       return true;  // Everyone authorized!
   });

   // Good: Check permissions
   Broadcast::channel('sensitive-data', function ($user) {
       return $user->hasRole('admin');
   });
   ```

4. **Don't Ignore Queue Failures**
   ```php
   // Bad: No retry logic
   class Notification extends Notification { }

   // Good: Configure retries
   class Notification extends Notification implements ShouldQueue
   {
       public $tries = 3;
       public $backoff = 60;  // seconds to wait before retry
   }
   ```

---

## Troubleshooting

### Q1: Bell icon tidak muncul

**Symptoms**: Header layout tidak menampilkan bell icon

**Solutions**:
1. Check component wrapper di layout:
   ```php
   // Cek di resources/views/layouts/app/sidebar.blade.php
   <x-notification-bell class="mr-2" />  // Should be there
   ```

2. Clear view cache:
   ```bash
   php artisan view:clear
   npm run build
   ```

3. Verify Livewire auto-discovery:
   ```bash
   php artisan livewire:list
   # Should show: App\Livewire\NotificationBell
   ```

### Q2: Real-time notifications tidak masuk

**Symptoms**: Notifikasi muncul di DB tapi tidak real-time di bell dropdown

**Check List**:
1. **Reverb server berjalan**: Check terminal output for `Reverb` process
   ```bash
   # Should see: Starting Reverb server...
   ```

2. **WebSocket connection established**: Open DevTools (F12) → Network → filter `WS`
   ```
   Should see: ws://localhost:8080/app/... → 101 Switching Protocols
   ```

3. **Echo initialized**: In browser console:
   ```javascript
   window.Echo  // Should exist
   ```

4. **Broadcast configured**: Check `.env`:
   ```
   BROADCAST_CONNECTION=reverb  // NOT 'log'
   ```

5. **Queue worker running**:
   ```bash
   # Check if queue process in composer dev is running
   queue    | Processing jobs from queue...
   ```

### Q3: WebSocket connection keeps disconnecting

**Solutions**:
1. **Increase timeout** in `config/reverb.php`:
   ```php
   'ping_interval' => 30,      // seconds between pings
   'ping_timeout' => 10,       // seconds to wait for pong
   ```

2. **Check firewall** — Reverb needs port 8080 open

3. **Verify CORS** if not on localhost:
   ```php
   // config/reverb.php
   'allowed_origins' => ['localhost:8080', 'your-domain.com'],
   ```

### Q4: Notifikasi duplikat atau tidak terupdate

**Solutions**:
1. **Check queue**: Notifications might be processing multiple times
   ```bash
   # Clear failed jobs
   php artisan queue:failed
   php artisan queue:failed --method=clear
   ```

2. **Verify component re-render**:
   ```php
   // In NotificationBell.php
   #[On('echo-private:App.Models.User.{userId},NotificationSent')]
   public function onNotificationReceived(array $event): void
   {
       $this->loadNotifications();  // Must reload from DB
   }
   ```

3. **Check wire:key**: Each notification needs unique key
   ```blade
   <div wire:key="notif-{{ $notif['id'] }}">
   ```

### Q5: Permission error saat subscribe ke channel

**Symptoms**: Console error `403 Forbidden` on `/broadcasting/auth`

**Solutions**:
1. **Ensure user authenticated**: Check `auth()->check()`

2. **Verify channel authorization** in `routes/channels.php`:
   ```php
   Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
       return (int) $user->id === (int) $id;  // Must return true/array
   });
   ```

3. **Check CSRF token** in meta tag:
   ```blade
   <!-- Must exist in head -->
   <meta name="csrf-token" content="{{ csrf_token() }}">
   ```

4. **Verify authEndpoint**:
   ```javascript
   // In resources/js/app.js
   authEndpoint: '/broadcasting/auth',  // Must exist
   ```

---

## Advanced Topics

### Creating Custom Notifications

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
    ) {
        $this->onQueue('notifications');  // Use custom queue
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toDatabase(): array
    {
        return [
            'title' => 'Order Shipped',
            'message' => "Order #{$this->order->id} has been shipped",
            'icon' => 'truck',
            'color' => 'green',
            'url' => route('orders.show', $this->order),
            'order_id' => $this->order->id,
            'tracking_number' => $this->order->tracking_number,
        ];
    }

    public function toBroadcast(): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Order Shipped',
            'message' => "Order #{$this->order->id} has been shipped",
        ]);
    }

    public function toMail(object $notifiable)
    {
        return (new MailMessage)
            ->line("Your order #{$this->order->id} has been shipped!")
            ->action('Track Order', route('orders.show', $this->order));
    }

    public function broadcastType(): string
    {
        return 'OrderShipped';
    }
}
```

### Listening to Specific Events

```php
// In Livewire component, listen to specific notification type
#[On('echo-private:App.Models.User.{userId},OrderShipped')]
public function onOrderShipped(array $event): void
{
    // Handle order shipped notification
    $this->dispatch('notification-toast',
        title: 'Order Shipped!',
        message: "Track your order #{$event['order_id']}",
    );
}
```

### Broadcast to Multiple Users

```php
// Notify all users in a company
$company->users->each(fn ($user) =>
    $user->notify(new CompanyAnnouncementNotification($announcement))
);

// Notify specific roles
User::role('admin')->each(fn ($user) =>
    $user->notify(new SystemAlertNotification($alert))
);
```

### Rate Limiting Notifications

```php
// Only notify if not recently notified
class CriticalAlertNotification extends Notification implements ShouldQueue
{
    public function via($notifiable): array
    {
        if ($notifiable->wasRecentlyNotified('alert')) {
            return [];  // Skip if recently notified
        }

        return ['database', 'broadcast'];
    }
}
```

---

## Kesimpulan

**Notification System dengan Laravel Reverb** adalah solusi modern untuk:
- 📲 Real-time notifications tanpa refresh
- 💾 Persistent history di database
- 🔒 Secure private channels per user
- ⚡ Async processing via queue
- 🎯 Easy integration dengan Livewire

**Key Files to Remember:**
- Notification class: `app/Notifications/*.php`
- Component: `app/Livewire/NotificationBell.php`
- View: `resources/views/livewire/notification-bell.blade.php`
- Routes: `routes/web.php`, `routes/channels.php`
- Config: `.env` BROADCAST_CONNECTION

**Next Steps:**
1. Create custom notifications untuk domain anda
2. Add email channel untuk critical notifications
3. Implement notification preferences/settings untuk users
4. Create reports/analytics dari notification data
5. Add notification categories/groups

---

## Reference Links

- [Laravel Notifications Docs](https://laravel.com/docs/13.x/notifications)
- [Laravel Reverb Docs](https://laravel.com/docs/13.x/reverb)
- [Broadcasting Guide](https://laravel.com/docs/13.x/broadcasting)
- [Livewire Docs](https://livewire.laravel.com/docs)
- [Flux UI Components](https://fluxui.dev/docs)

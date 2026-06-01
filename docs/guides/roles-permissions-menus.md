# Role, Permission, dan Menu menggunakan Spatie Permission

Panduan komprehensif untuk memahami dan mengimplementasikan sistem Role, Permission, dan Menu dalam aplikasi Laravel Livewire starter kit menggunakan Spatie Permission.

## Daftar Isi

1. [Konsep Dasar](#konsep-dasar)
2. [Struktur File dan Folder](#struktur-file-dan-folder)
3. [Model dan Database](#model-dan-database)
4. [Implementasi dalam Kode](#implementasi-dalam-kode)
5. [Menambah Role dan Permission Baru](#menambah-role-dan-permission-baru)
6. [Menggunakan Permission di Views dan Controllers](#menggunakan-permission-di-views-dan-controllers)
7. [Best Practices](#best-practices)

---

## Konsep Dasar

### Role (Peran)

**Role** adalah sekumpulan **permissions** yang dikelompokkan berdasarkan tanggung jawab atau posisi dalam organisasi.

**Contoh Role dalam aplikasi:**
- `superadmin` — akses penuh ke semua fitur
- `administrator` — mengelola pengguna, role, menu, dan laporan
- `sales` — mengelola penjualan, customer, dan CRM
- `inventory` — mengelola produk, warehouse, dan stock
- `accounting` — mengelola transaksi, pembayaran, dan laporan keuangan
- `staff` — akses terbatas, hanya dashboard

### Permission (Izin)

**Permission** adalah aksi spesifik yang dapat dilakukan oleh pengguna terhadap sebuah resource. Permission mengikuti konvensi penamaan:

```
<resource>.<action>
```

**Contoh:**
- `users.view` — melihat daftar pengguna
- `users.create` — membuat pengguna baru
- `users.edit` — mengubah pengguna
- `users.delete` — menghapus pengguna
- `users.assign_roles` — assign role ke pengguna

**Standard Actions:**
- `view` — melihat/membaca data
- `create` — membuat data baru
- `edit` — mengubah/update data
- `delete` — menghapus data
- Custom actions: `approve`, `post`, `void`, `convert`, `export`

### Menu (Navigasi)

**Menu** adalah elemen navigasi yang ditampilkan di sidebar aplikasi. Setiap menu terhubung dengan satu atau lebih permissions, sehingga menu hanya ditampilkan jika user memiliki permission yang diperlukan.

**Struktur Menu:**
- **Root Menu** — menu level 0, muncul langsung di sidebar
- **Group Menu** — menu level 0 tanpa route, berfungsi sebagai kategori (contoh: "Master Data", "Sales")
- **Child Menu** — menu level 1, anak dari group menu

**Hubungan Ketiga Konsep:**

```
User 
  ↓ (memiliki)
Role (superadmin, administrator, sales, etc.)
  ↓ (memiliki)
Permission (users.view, users.create, etc.)
  ↓ (ditampilkan di)
Menu (Users, Products, etc.)
```

---

## Struktur File dan Folder

### Direktori Utama

```
app/
├── Models/
│   ├── User.php                    ← Menggunakan trait HasRoles dari Spatie
│   ├── Role.php                    ← Extends SpatieRole
│   ├── Permission.php              ← Extends SpatiePermission
│   └── Menu.php                    ← Model untuk navigasi
│
├── Concerns/                       ← Traits untuk validasi
│   ├── PasswordValidationRules.php
│   └── ProfileDataValidationRules.php
│
└── Livewire/                       ← Komponen Livewire untuk management
    ├── (future) Roles/
    ├── (future) Permissions/
    └── (future) Menus/

database/
├── migrations/
│   ├── *_create_users_table.php         ← User table
│   ├── *_create_roles_table.php         ← Spatie Role table (auto-created)
│   ├── *_create_permissions_table.php   ← Spatie Permission table (auto-created)
│   └── *_create_menus_table.php         ← Menu table (custom)
│
└── seeders/
    ├── RolesPermissionsSeeder.php       ← Definisikan roles & permissions
    └── MenuSeeder.php                   ← Definisikan menu & hubungan dengan permission
```

---

## Model dan Database

### User Model

**File:** `app/Models/User.php`

```php
<?php

namespace App\Models;

use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    use HasFactory, TwoFactorAuthenticatable, HasRoles, // ← Trait Spatie untuk roles
        LogsActivity, InteractsWithMedia;

    // User dapat memiliki multiple roles
    // HasRoles trait menyediakan methods:
    // - $user->assignRole('admin')
    // - $user->syncRoles(['admin', 'editor'])
    // - $user->hasRole('admin')
    // - $user->can('users.view')
    // - $user->hasPermissionTo('users.create')
}
```

**Methods yang tersedia dari `HasRoles` trait:**

```php
// Assign/manage roles
$user->assignRole('admin');                    // Assign satu role
$user->assignRole(['admin', 'editor']);        // Assign multiple roles
$user->syncRoles(['admin']);                   // Replace semua roles dengan yang baru
$user->removeRole('admin');                    // Hapus satu role

// Check roles
$user->hasRole('admin');                       // boolean
$user->hasAnyRole(['admin', 'editor']);        // boolean
$user->hasAllRoles(['admin', 'editor']);       // boolean
$user->roles()->get();                         // Get all roles

// Check permissions (inherited dari roles)
$user->can('users.view');                      // boolean
$user->hasPermissionTo('users.create');        // boolean
$user->hasAnyPermission(['users.view', 'users.create']); // boolean
```

### Role Model

**File:** `app/Models/Role.php`

```php
<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Role extends SpatieRole
{
    use LogsActivity;

    // Table: roles
    // Columns: id, name, guard_name, created_at, updated_at

    // Methods dari Spatie:
    // - $role->givePermissionTo($permission)
    // - $role->syncPermissions([$permission1, $permission2])
    // - $role->hasPermissionTo($permission)
}
```

### Permission Model

**File:** `app/Models/Permission.php`

```php
<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = ['name', 'guard_name', 'menu_id', 'sort_order'];

    // Table: permissions
    // Columns: id, name, guard_name, menu_id, sort_order, created_at, updated_at

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
```

### Menu Model

**File:** `app/Models/Menu.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'name',         // Nama menu yang ditampilkan (contoh: "Users", "Products")
        'slug',         // Identifier unik (contoh: "users", "products")
        'icon',         // Icon Heroicons (contoh: "user", "shopping-bag")
        'route_name',   // Nama route (contoh: "users.index")
        'route_pattern', // Pattern untuk active state (contoh: "users.*")
        'parent_id',    // ID menu parent (untuk nested menus)
        'level',        // 0 untuk root/group, 1 untuk child
        'sort_order',   // Urutan tampilan
        'layout',       // Layout tempat menu muncul (sidebar, nav_user)
        'is_active'     // Apakah menu aktif/ditampilkan
    ];

    public function permissions()
    {
        return $this->hasMany(Permission::class)->orderBy('sort_order');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order');
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function scopeRoots($query)
    {
        return $query->where('level', 0)->orderBy('sort_order');
    }
}
```

---

## Implementasi dalam Kode

### 1. Database Migrations

Spatie Permission secara otomatis membuat tabel `roles` dan `permissions` saat package diinstal. Aplikasi ini juga memiliki tabel `menus` custom:

**Tabel Struktur:**

```
users
├── id
├── email
├── password
├── name
├── is_active
└── ... (columns lain)

roles
├── id
├── name (superadmin, administrator, sales, etc.)
├── guard_name (web)
└── created_at, updated_at

permissions
├── id
├── name (users.view, users.create, etc.)
├── guard_name (web)
├── menu_id (FK → menus.id)
├── sort_order
└── created_at, updated_at

menus
├── id
├── name
├── slug
├── icon
├── route_name
├── route_pattern
├── parent_id (FK → menus.id, nullable untuk root menus)
├── level (0 atau 1)
├── sort_order
├── layout (sidebar, nav_user)
├── is_active
└── created_at, updated_at

role_has_permissions (junction table - Spatie auto-created)
├── permission_id (FK → permissions.id)
├── role_id (FK → roles.id)

model_has_roles (junction table - Spatie auto-created)
├── role_id (FK → roles.id)
├── model_id (FK → users.id)
└── model_type (User)
```

### 2. RolesPermissionsSeeder

**File:** `database/seeders/RolesPermissionsSeeder.php`

Seeder ini mendefinisikan semua **permissions** dan **roles**, serta menghubungkan permissions ke roles.

**Struktur:**

```php
class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. Definisikan semua permissions dengan struktur resource.action
        $resources = [
            'users' => ['view', 'create', 'edit', 'delete', 'assign_roles'],
            'roles' => ['view', 'create', 'edit', 'delete'],
            'products' => ['view', 'create', 'edit', 'delete'],
            // ... resource lainnya
        ];

        // 2. Create permissions
        foreach ($resources as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$resource}.{$action}",
                    'guard_name' => 'web'
                ]);
            }
        }

        // 3. Create roles
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        // 4. Assign permissions to roles
        Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web'])
            ->givePermissionTo([
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
                // ... permissions lainnya
            ]);
    }
}
```

**Alur:**
1. Hapus cache permissions untuk refresh semua permissions (penting saat update)
2. Definisikan semua permissions yang ada dalam aplikasi
3. Create roles
4. Assign permissions ke masing-masing role

### 3. MenuSeeder

**File:** `database/seeders/MenuSeeder.php`

Seeder ini membuat struktur menu dan menghubungkannya dengan permissions.

**Struktur:**

```php
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Root menu (level 0, tampil langsung di sidebar)
        $dashboard = $this->menu('dashboard', [
            'name' => 'Dashboard',
            'icon' => 'home',
            'route_name' => 'dashboard',
            'sort_order' => 1,
        ]);
        // Attach permissions yang diperlukan untuk menu ini
        $this->attachPerms($dashboard, ['dashboard.view']);

        // Group menu (level 0, kategori tanpa route)
        $masterData = $this->group('master-data', 'Master Data', 'cog-6-tooth', 3);

        // Child menu (level 1, anak dari group)
        $users = $this->child('users', 'Users', 'user', $masterData, 1,
            'users.index', 'users.*');
        $this->attachPerms($users, [
            'users.view', 'users.create', 'users.edit', 'users.delete'
        ]);
    }

    // Helper: Create/get root menu
    private function menu(string $slug, array $attrs): Menu { ... }

    // Helper: Create/get group menu (kategori)
    private function group(string $slug, string $name, string $icon, int $sort): Menu { ... }

    // Helper: Create/get child menu
    private function child(
        string $slug, string $name, string $icon, Menu $parent, int $sort,
        ?string $routeName = null, ?string $routePattern = null
    ): Menu { ... }

    // Helper: Attach permissions ke menu
    private function attachPerms(Menu $menu, array $names): void { ... }
}
```

**Hierarchy Menu:**

```
Dashboard (level 0, root)

Master Data (level 0, group/kategori)
├── Companies (level 1, child)
├── Branches (level 1, child)
└── Parameters (level 1, child)

Sales (level 0, group/kategori)
├── Quotations (level 1, child)
├── Sales Orders (level 1, child)
├── Sales Invoices (level 1, child)
└── Customers (level 1, child)

[Lebih banyak groups & menus...]
```

---

## Menambah Role dan Permission Baru

### Skenario: Menambah Role "Supervisor" dan Permission Baru untuk "Approvals"

#### Step 1: Update RolesPermissionsSeeder

```php
class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $crud = ['view', 'create', 'edit', 'delete'];

        $resources = [
            // ... existing resources ...

            // Tambahan: Approvals
            'approvals' => ['view', 'approve', 'reject'],
        ];

        // Create all permissions
        $allPermissions = [];
        foreach ($resources as $resource => $actions) {
            foreach ($actions as $action) {
                $name = "{$resource}.{$action}";
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                $allPermissions[] = $name;
            }
        }

        // ... existing roles ...

        // Tambahan: Role Supervisor
        Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web'])
            ->givePermissionTo([
                'dashboard.view',
                'sales_quotations.view', 'sales_quotations.approve',
                'sales_orders.view', 'sales_orders.approve',
                'purchase_requests.view', 'purchase_requests.approve',
                'approvals.view', 'approvals.approve', 'approvals.reject',
                'activity_logs.view',
                'reports_sales.view',
            ]);
    }
}
```

#### Step 2: Update MenuSeeder

```php
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // ... existing menus ...

        // Tambahan: Group untuk approvals
        $approvals = $this->group('approvals', 'Approvals', 'checkbox', 5);

        $approvalMenu = $this->child(
            'approvals',
            'Pending Approvals',
            'clipboard-document-check',
            $approvals,
            1,
            'approvals.index',
            'approvals.*'
        );
        $this->attachPerms($approvalMenu, [
            'approvals.view', 'approvals.approve', 'approvals.reject'
        ]);
    }
}
```

#### Step 3: Run Seeders

```bash
php artisan migrate:fresh --seed
```

---

## Menggunakan Permission di Views dan Controllers

### 1. Check Permission di Controller

**Menggunakan method `authorize()`:**

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Authorize menggunakan permission
        // Akan throw AuthorizationException jika user tidak memiliki permission
        $this->authorize('users.view');

        $users = User::all();
        return view('users.index', ['users' => $users]);
    }

    public function show(User $user)
    {
        $this->authorize('users.view');
        return view('users.show', ['user' => $user]);
    }

    public function create()
    {
        $this->authorize('users.create');
        return view('users.create');
    }

    public function store(Request $request)
    {
        $this->authorize('users.create');

        // Create user logic
        $user = User::create($request->validated());

        return redirect()->route('users.index')->with('success', 'User created');
    }

    public function edit(User $user)
    {
        $this->authorize('users.edit');
        return view('users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('users.edit');

        // Update user logic
        $user->update($request->validated());

        return redirect()->route('users.index')->with('success', 'User updated');
    }

    public function destroy(User $user)
    {
        $this->authorize('users.delete');

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted');
    }
}
```

**Menggunakan `can()` method:**

```php
public function index()
{
    // Menggunakan can() method
    if (!auth()->user()->can('users.view')) {
        abort(403, 'Unauthorized');
    }

    $users = User::all();
    return view('users.index', ['users' => $users]);
}

// Atau menggunakan helper
public function store(Request $request)
{
    if (!auth()->check() || !auth()->user()->can('users.create')) {
        abort(403);
    }

    // Create user logic
}
```

### 2. Check Permission di Blade Views

**Directive `@can`:**

```blade
<!-- Tampilkan hanya jika user memiliki permission users.create -->
@can('users.create')
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        Add User
    </a>
@endcan

<!-- Dengan else -->
@can('users.edit')
    <button class="btn btn-sm btn-warning">Edit</button>
@else
    <span class="text-muted">Cannot edit</span>
@endcan

<!-- Multiple permissions (AND logic) -->
@can('users.edit', $user)
    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning">
        Edit
    </a>
@endcan
```

**Directive `@cannot`:**

```blade
<!-- Kebalikan dari @can -->
@cannot('users.delete')
    <p class="alert alert-warning">You cannot delete users</p>
@endcannot
```

**Menggunakan `can()` helper dalam conditional:**

```blade
@if(auth()->user()->can('users.view'))
    <div class="users-section">
        <!-- Users content -->
    </div>
@endif

<!-- Atau dengan role check -->
@if(auth()->user()->hasRole('administrator'))
    <div class="admin-panel">
        <!-- Admin-only content -->
    </div>
@endif
```

### 3. Check Permission dalam Livewire Components

**Livewire Component:**

```php
<?php

namespace App\Livewire\Pages\Users;

use Livewire\Component;
use App\Models\User;

class Index extends Component
{
    public function mount()
    {
        // Check permission saat component di-mount
        $this->authorize('users.view');
    }

    public function deleteUser(User $user)
    {
        // Check permission sebelum action
        $this->authorize('users.delete');

        $user->delete();

        $this->dispatch('user-deleted', ['message' => 'User deleted successfully']);
    }

    public function render()
    {
        return view('livewire.pages.users.index', [
            'users' => User::all(),
        ]);
    }
}
```

**Livewire Blade View:**

```blade
<div>
    @can('users.create')
        <button wire:click="$dispatch('openCreateModal')" class="btn btn-primary">
            Add User
        </button>
    @endcan

    <table class="table">
        @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @can('users.edit')
                        <button wire:click="editUser({{ $user->id }})" class="btn btn-sm btn-warning">
                            Edit
                        </button>
                    @endcan

                    @can('users.delete')
                        <button wire:click="deleteUser({{ $user->id }})" class="btn btn-sm btn-danger">
                            Delete
                        </button>
                    @endcan
                </td>
            </tr>
        @endforeach
    </table>
</div>
```

---

## Best Practices

### 1. Permission Naming Convention

Selalu gunakan format `<resource>.<action>`:

```php
// ✅ BAIK
'users.view'
'users.create'
'users.edit'
'users.delete'
'users.assign_roles'

// ❌ BURUK
'view_users'
'user_create'
'canDeleteUser'
'manage_users'
```

### 2. Standardisasi Actions

Gunakan action yang konsisten across semua resources:

```php
// Standard CRUD actions
$crud = ['view', 'create', 'edit', 'delete'];

// Custom actions untuk specific resources
'users' => [...$crud, 'assign_roles'],
'sales_orders' => [...$crud, 'approve'],
'transactions' => [...$crud, 'post', 'void'],
```

### 3. Menu-Permission Relationship

Setiap menu harus memiliki minimal satu permission yang terhubung:

```php
// ✅ BAIK: Menu memiliki associated permissions
$users = $this->menu('users', [
    'name' => 'Users',
    'route_name' => 'users.index',
]);
$this->attachPerms($users, ['users.view']);

// ❌ BURUK: Menu tanpa permissions
$orphanedMenu = Menu::create(['name' => 'Orphaned']);
```

### 4. Role Assignment Strategy

**Principle of Least Privilege:** Assign hanya permissions yang diperlukan:

```php
// ✅ BAIK: Minimal permissions
Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web'])
    ->givePermissionTo(['dashboard.view']);

// ❌ BURUK: Terlalu banyak permissions
Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web'])
    ->givePermissionTo($allPermissions); // Jangan lakukan ini!
```

### 5. Guard Name

Selalu specify `guard_name` ketika membuat roles dan permissions:

```php
// ✅ BAIK: Specify guard_name
Permission::firstOrCreate([
    'name' => 'users.view',
    'guard_name' => 'web'  // ← Explicit
]);

// ⚠️ KURANG BAIK: Rely pada default
Permission::firstOrCreate(['name' => 'users.view']);
```

### 6. Cache Management

Clear cache setelah membuat/mengubah permissions:

```php
// Setelah create/update/delete permissions
app(PermissionRegistrar::class)->forgetCachedPermissions();

// Atau menggunakan artisan command
php artisan cache:clear
```

### 7. Authorization di Routes

Implementasikan middleware untuk group routes:

```php
// routes/web.php

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('can:dashboard.view')
        ->name('dashboard');

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->middleware('can:users.view')
            ->name('users.index');

        Route::post('/', [UserController::class, 'store'])
            ->middleware('can:users.create')
            ->name('users.store');

        // ... other routes
    });
});
```

### 8. Super Admin Handling

Superadmin secara otomatis dapat melakukan semua aksi. Konfigurasi ini ada di `config/permission.php`:

```php
// config/permission.php
'permission_model' => Spatie\Permission\Models\Permission::class,

// Di AuthServiceProvider atau middleware, setup gate:
Gate::before(function ($user) {
    return $user->hasRole('superadmin') ? true : null;
});
```

### 9. Testing Permissions

Ketika membuat tests, selalu test permission checks:

```php
// tests/Feature/UserTest.php
use Spatie\Permission\Models\Role;

class UserTest extends TestCase
{
    public function test_user_with_permission_can_view_users()
    {
        $user = User::factory()->create();
        $user->assignRole('administrator');

        $response = $this->actingAs($user)->get('/users');

        $response->assertOk();
    }

    public function test_user_without_permission_cannot_view_users()
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        $response = $this->actingAs($user)->get('/users');

        $response->assertForbidden();
    }
}
```

### 10. Dokumentasi Role Capabilities

Dokumentasikan apa yang setiap role dapat lakukan:

```markdown
## Role Capabilities

### Superadmin
- Akses penuh ke semua fitur

### Administrator
- Manage users (view, create, edit, delete, assign roles)
- Manage roles dan permissions
- Manage menus
- View activity logs
- View semua reports

### Sales
- View dan manage sales quotations
- View dan manage sales orders
- View dan manage customers
- View dan manage leads, contacts, opportunities

### Inventory
- View dan manage products
- View dan manage warehouses
- Approve stock movements dan adjustments

### Supervisor
- View dan approve quotations, orders, purchase requests
- View approvals dashboard
```

---

## Troubleshooting

### Permission Tidak Bekerja

**Problem:** User memiliki role tapi permission check tetap gagal.

**Solusi:**
1. Clear cache: `php artisan cache:clear`
2. Jalankan ulang seeder: `php artisan migrate:fresh --seed`
3. Verify permission di database: `select * from permissions where name = 'users.view'`
4. Verify role-permission relationship: `select * from role_has_permissions where role_id = 1`

### Menu Tidak Muncul

**Problem:** Menu tidak ditampilkan di sidebar meski user memiliki permission.

**Solusi:**
1. Check menu `is_active` flag (harus `true`)
2. Verify menu `layout` field (harus `sidebar` atau `nav_user`)
3. Verify menu-permission relationship: `select * from permissions where menu_id = 1`
4. Check parent menu (jika child menu, parent harus visible juga)

### Authorization Exception di Production

**Problem:** Unexpected "Unauthorized" errors di production.

**Solusi:**
1. Run migration: `php artisan migrate`
2. Run seeder: `php artisan db:seed --class=RolesPermissionsSeeder`
3. Clear config cache: `php artisan config:cache`
4. Check user roles: `select * from model_has_roles where model_id = <user_id>`

---

## Resources & Links

- [Spatie Laravel Permission Documentation](https://spatie.be/docs/laravel-permission/v6/introduction)
- [Laravel Authorization (Gates & Policies)](https://laravel.com/docs/11.x/authorization)
- [Laravel Blade Templates](https://laravel.com/docs/11.x/blade)
- [Livewire Documentation](https://livewire.laravel.com/)

# Parameter Feature

Parameter adalah sistem **key-value store** fleksibel untuk menyimpan enum/lookup dinamis seperti gender, agama, status pernikahan, dan sejenisnya. Data ini dikelola via UI admin tanpa perlu mengubah kode atau migrasi baru.

---

## Struktur Database

Tabel `parameters` menggunakan UUID sebagai primary key.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | UUID (PK) | Primary key, di-generate manual via `Str::uuid()` |
| `group` | string(50) | Nama kelompok, e.g. `gender`, `religion` |
| `code` | string(100) | Kode unik (unique constraint), e.g. `male`, `islam` |
| `value` | string(150) | Label tampilan, e.g. `Male`, `Islam` |
| `description` | text | Opsional, keterangan tambahan |
| `text_color` | string(10) | Hex color untuk teks badge, e.g. `#ffffff` |
| `bg_color` | string(10) | Hex color untuk latar badge, e.g. `#16a34a` |
| `attributes` | JSON | Data tambahan bebas (cast ke array) |
| `is_system` | boolean | Jika `true`, tidak bisa dihapus via UI |
| `is_active` | boolean | Jika `false`, tidak muncul di dropdown |
| `sort_order` | integer | Urutan tampil |
| `created_by` / `updated_by` / `deleted_by` | FK users | Audit trail otomatis |

---

## Model

File: [app/Models/Parameter.php](../../app/Models/Parameter.php)

### Konfigurasi penting

```php
public $incrementing = false;   // UUID, bukan auto-increment
protected $keyType = 'string';
```

### Scope yang tersedia

```php
// Ambil berdasarkan group
Parameter::group('religion')->get();

// Ambil yang aktif saja
Parameter::active()->get();

// Shortcut: group + active + order by sort_order
Parameter::ofGroup('religion');
```

### Helper method `ofGroup()`

```php
// Mengembalikan Collection yang sudah difilter: aktif, diurutkan
public static function ofGroup(string $group): Collection
{
    return static::group($group)->active()->orderBy('sort_order')->get();
}
```

---

## Cara Menggunakan di Kode

### 1. Mengisi dropdown pada form

Pola yang dipakai pada halaman Users (create/edit):

```php
// Di method mount() atau computed property Livewire
$this->religions = Parameter::group('religion')
    ->active()
    ->orderBy('sort_order')
    ->get(['id', 'value as name'])
    ->toArray();

$this->maritalStatuses = Parameter::group('marital_status')
    ->active()
    ->orderBy('sort_order')
    ->get(['id', 'value as name'])
    ->toArray();
```

Lalu di Blade:

```blade
<flux:select wire:model="religion_id" label="Religion">
    <flux:option value="">-- Select --</flux:option>
    @foreach ($religions as $item)
        <flux:option value="{{ $item['id'] }}">{{ $item['name'] }}</flux:option>
    @endforeach
</flux:select>
```

### 2. Relasi dari model lain

Model [Profile](../../app/Models/Profile.php) menyimpan `religion_id` dan `marital_status_id` sebagai FK ke tabel `parameters`:

```php
public function religion(): BelongsTo
{
    return $this->belongsTo(Parameter::class, 'religion_id');
}

public function maritalStatus(): BelongsTo
{
    return $this->belongsTo(Parameter::class, 'marital_status_id');
}
```

Penggunaan di Blade:

```blade
{{ $user->profile->religion?->value }}
{{ $user->profile->maritalStatus?->value }}
```

### 3. Ambil satu nilai berdasarkan code

```php
// Ambil UUID dari parameter dengan code tertentu
$islamId = Parameter::where('code', 'islam')->value('id');

// Gunakan saat seeding
'religion_id' => Parameter::where('code', 'islam')->value('id'),
```

### 4. Tampilkan badge berwarna

Parameter mendukung `text_color` dan `bg_color` untuk rendering badge inline:

```blade
@if ($param->bg_color && $param->text_color)
    <span
        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
        style="background-color:{{ $param->bg_color }};color:{{ $param->text_color }}"
    >{{ $param->value }}</span>
@else
    {{ $param->value }}
@endif
```

---

## Menambah Group Baru

### Langkah 1 — Tambahkan ke ParameterSeeder

File: [database/seeders/ParameterSeeder.php](../../database/seeders/ParameterSeeder.php)

```php
'education_level' => [
    ['code' => 'sd',   'value' => 'SD'],
    ['code' => 'smp',  'value' => 'SMP'],
    ['code' => 'sma',  'value' => 'SMA'],
    ['code' => 's1',   'value' => 'S1'],
    ['code' => 's2',   'value' => 'S2'],
    ['code' => 's3',   'value' => 'S3'],
],
```

Jalankan seeder:

```bash
php artisan db:seed --class=ParameterSeeder
```

### Langkah 2 — Daftarkan di halaman admin Parameters

File: [resources/views/pages/configurations/parameters/⚡index.blade.php](../../resources/views/pages/configurations/parameters/⚡index.blade.php)

Tambahkan ke array `$groups` di Livewire component:

```php
public array $groups = [
    'gender'          => 'Gender',
    'religion'        => 'Religion',
    'marital_status'  => 'Marital Status',
    'education_level' => 'Education Level', // tambahkan di sini
];
```

### Langkah 3 — Tambahkan FK di tabel terkait (opsional)

Jika group baru dipakai sebagai referensi di tabel lain, buat migrasi baru:

```php
$table->uuid('education_level_id')->nullable();
$table->foreign('education_level_id')->references('id')->on('parameters')->nullOnDelete();
```

Lalu tambahkan relasi di model terkait:

```php
public function educationLevel(): BelongsTo
{
    return $this->belongsTo(Parameter::class, 'education_level_id');
}
```

---

## Membuat Parameter via Kode (tanpa UI)

```php
use App\Models\Parameter;
use Illuminate\Support\Str;

Parameter::create([
    'id'         => (string) Str::uuid(),
    'group'      => 'education_level',
    'code'       => 's1',
    'value'      => 'S1 / Bachelor',
    'is_system'  => true,   // tidak bisa dihapus via UI
    'is_active'  => true,
    'sort_order' => 4,
]);
```

---

## Aturan Bisnis

| Kondisi | Perilaku |
|---------|----------|
| `is_system = true` | Tidak bisa dihapus via UI, tombol Delete disembunyikan |
| `is_active = false` | Tidak muncul di hasil `ofGroup()` dan query dengan scope `active()` |
| `code` | Harus unik di seluruh tabel (bukan per group), gunakan format `snake_case` |
| UUID sebagai PK | Harus di-generate manual: `'id' => (string) Str::uuid()` |
| Soft delete | Data tidak hilang permanen, tetap bisa di-restore via Tinker |
| `deleted_by` | Di-set otomatis oleh model observer di method `booted()` |

---

## Halaman Admin

Route: `/configurations/parameters`  
Permission: `parameters.view`, `parameters.create`, `parameters.edit`, `parameters.delete`

Fitur UI yang tersedia:
- Tab per group untuk navigasi cepat
- Search realtime (debounce 300ms) berdasarkan code atau value
- Sort kolom: code, value, sort_order
- Pagination 15 item per halaman
- Form modal (flyout) untuk create dan edit
- Preview badge warna langsung di form
- Konfirmasi sebelum delete
- Proteksi: parameter `is_system` tidak bisa dihapus

---

## Groups yang Sudah Ada

| Group | Dipakai di |
|-------|-----------|
| `gender` | `users.gender` (langsung di tabel users, stored as string `male`/`female`) |
| `religion` | `profiles.religion_id` (FK ke `parameters.id`) |
| `marital_status` | `profiles.marital_status_id` (FK ke `parameters.id`) |

> **Catatan:** Field `gender` di tabel `users` menyimpan nilai `code` langsung (string), bukan UUID, karena kolom ini dibuat sebelum sistem Parameter diterapkan. Untuk group baru, gunakan pola FK seperti `religion_id`.

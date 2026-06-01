# Global File Upload System dengan Spatie Media Library

## 📋 Daftar Isi
- [Pengenalan](#pengenalan)
- [Arsitektur Sistem](#arsitektur-sistem)
- [Struktur File & Folder](#struktur-file--folder)
- [Implementasi](#implementasi)
- [Penggunaan](#penggunaan)
- [Contoh: Avatar User](#contoh-avatar-user)
- [Contoh: Feature Baru](#contoh-feature-baru)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Pengenalan

**File Upload System** adalah infrastruktur terpusat untuk mengelola upload file di aplikasi Laravel ini. Sistem ini dibangun dengan **Spatie Laravel Media Library**, library populer yang menyediakan:

- ✅ Manajemen file polimorfik (satu sistem untuk semua model)
- ✅ Penyimpanan otomatis ke disk yang dikonfigurasi
- ✅ Image conversions (resize, thumbnail, dll)
- ✅ Tracking metadata (nama asli, mime type, ukuran, dll)
- ✅ Penghapusan file otomatis

### Mengapa Spatie Media Library?

| Aspek | Benefit |
|-------|---------|
| **Satu pola untuk semua** | Tidak perlu tambah kolom `avatar_url`, `logo_url` di tiap tabel — pakai `media` table |
| **Polimorfik** | User bisa punya avatar, Company punya logo, Employee punya dokumen — semua pakai relasi `->media()` |
| **Metadata tracking** | Tahu siapa upload, kapan, tipe file, ukuran, nama asli |
| **Otomatis cleanup** | `singleFile()` otomatis hapus file lama saat yang baru diupload |
| **Conversions** | Generate thumbnail, resize image, dll otomatis |

---

## 🏗️ Arsitektur Sistem

```
┌─────────────────────────────────────────────────┐
│         Livewire Volt Component                 │
│   (Profile settings, Company form, dll)         │
└────────────────┬────────────────────────────────┘
                 │
                 ├─ use HasFileUpload trait
                 ├─ use FileUploadValidationRules trait
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│   Flux File-Upload UI Component                 │
│   (Drag-drop, file select, preview)             │
│   wire:model="uploadedFile"                     │
└────────────────┬────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│   Livewire WithFileUploads                      │
│   (Temporary file storage on server)            │
└────────────────┬────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│   Model (User, Company, Employee, dll)          │
│   implements HasMedia                           │
│   use InteractsWithMedia (Spatie)               │
│   registerMediaCollections()                    │
└────────────────┬────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│   Spatie Media Library                          │
│   - Storage di public/media/                    │
│   - Record di tabel media                       │
│   - Relationship ke model via model_type        │
└─────────────────────────────────────────────────┘
```

### Alur Kerja Upload

```
1. User select file di UI
   ↓
2. Flux file-upload kirim ke Livewire temporary storage
   ↓
3. Preview ditampilkan via Alpine FileReader (lokal)
   ↓
4. User klik "Upload" → call updateAvatar() method
   ↓
5. Validasi: image, mimes, max size
   ↓
6. Spatie Media Library:
   - Delete file lama (jika singleFile())
   - Store file ke public/media/avatars/uuid_timestamp.jpg
   - Create record di tabel media
   ↓
7. Toast success, form cleared, avatar muncul di nav
```

---

## 📁 Struktur File & Folder

### Core System Files

```
app/
├── Concerns/
│   ├── HasFileUpload.php                    ← Trait: Livewire upload capability
│   ├── FileUploadValidationRules.php        ← Trait: Named validation rules
│   ├── ProfileValidationRules.php           (existing)
│   └── PasswordValidationRules.php          (existing)
│
├── Models/
│   ├── User.php                             ← implements HasMedia, registerMediaCollections()
│   ├── Company.php                          (dapat di-extend dengan HasMedia)
│   └── ...
│
└── Providers/
    └── AppServiceProvider.php               (unchanged)

config/
├── filesystems.php                          ← Disk 'media' configured
├── media-library.php                        ← disk_name set to 'media'
└── ...

database/
└── migrations/
    └── 2026_05_29_141500_create_media_table.php  ← Spatie migration

resources/
├── views/
│   ├── pages/settings/
│   │   └── ⚡profile.blade.php              ← Avatar upload UI + methods
│   │
│   ├── components/
│   │   ├── desktop-user-menu.blade.php      ← Show avatar in nav
│   │   └── desktop-profile-menu.blade.php   ← Show avatar in sidebar
│   │
│   └── flux/
│       └── file-upload/
│           └── index.blade.php              ← Patched: wire passthrough
│
└── (npm assets, styles, etc)

public/
├── media/                                   ← UPLOADED FILES GO HERE
│   └── avatars/
│       ├── uuid_timestamp.jpg
│       ├── uuid_timestamp.png
│       └── ...
│
└── storage → symlink ke storage/app/public
```

### Database Tables

#### `media` table (created by Spatie migration)

```sql
CREATE TABLE media (
    id BIGINT PRIMARY KEY,
    model_type VARCHAR(255),          -- e.g., "App\Models\User"
    model_id BIGINT,                  -- e.g., user ID
    collection_name VARCHAR(255),     -- e.g., "avatar", "logo", "documents"
    name VARCHAR(255),                -- display name
    file_name VARCHAR(255),           -- stored filename (uuid_timestamp.jpg)
    mime_type VARCHAR(255),           -- e.g., "image/jpeg"
    disk VARCHAR(255),                -- "media"
    size BIGINT,                      -- file size in bytes
    manipulations JSON,               -- conversions metadata
    custom_properties JSON,           -- extra data
    generated_conversions JSON,       -- generated variants
    responsive_images JSON,           -- responsive image variants
    order_column INT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Contoh record:
-- id: 1, model_type: "App\Models\User", model_id: 5, 
-- collection_name: "avatar", file_name: "abc123_1234567890.jpg",
-- mime_type: "image/jpeg", disk: "media"
```

---

## 🔧 Implementasi

### 1. Instalasi (Sudah Selesai)

```bash
# Install Spatie
composer require spatie/laravel-medialibrary

# Publish migrations & config
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-config"

# Run migrations
php artisan migrate
```

### 2. Konfigurasi Disk (Sudah Selesai)

**`config/filesystems.php`** — tambah disk `media`:

```php
'disks' => [
    // ... existing disks ...
    
    'media' => [
        'driver'     => 'local',
        'root'       => public_path('media'),
        'url'        => rtrim(env('APP_URL', 'http://localhost'), '/').'/media',
        'visibility' => 'public',
        'throw'      => false,
    ],
],
```

**`config/media-library.php`** — set disk name:

```php
'disk_name' => env('MEDIA_DISK', 'media'),
```

**`.gitignore`** — exclude uploaded files:

```
/public/media
```

**`.env`** — pastikan APP_URL sesuai dengan server yang digunakan:

```env
# Untuk local dev dengan `composer dev`
APP_URL=http://localhost:8000

# Atau untuk Valet (jika menggunakan domain lokal)
APP_URL=https://laravel-app.test
```

⚠️ **Penting:** APP_URL digunakan untuk generate full URL ke media files. Jika mismatch dengan URL yang Anda akses, image tidak akan load (browser akan coba fetch dari domain yang salah).

Setelah ubah APP_URL, jalankan:
```bash
php artisan config:clear
```

### 3. Base Traits (Reusable Mixin)

#### `app/Concerns/HasFileUpload.php`

```php
<?php

namespace App\Concerns;

use Livewire\WithFileUploads;

trait HasFileUpload
{
    use WithFileUploads;

    // Untyped — Livewire assigns TemporaryUploadedFile at upload time
    public $uploadedFile = null;
}
```

Trait ini dipakai di **semua Volt component** yang butuh upload. Menyediakan:
- `WithFileUploads` dari Livewire
- Property `$uploadedFile` untuk menyimpan file sementara

#### `app/Concerns/FileUploadValidationRules.php`

```php
<?php

namespace App\Concerns;

trait FileUploadValidationRules
{
    protected function imageUploadRules(int $maxKb = 2048): array
    {
        return ['uploadedFile' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:' . $maxKb]];
    }

    protected function imageUploadMessages(): array
    {
        return [
            'uploadedFile.required' => __('Please select an image first.'),
            'uploadedFile.image' => __('The file must be a valid image (JPG, PNG, WebP, or GIF).'),
            'uploadedFile.mimes' => __('The file must be JPG, PNG, WebP, or GIF.'),
            'uploadedFile.max' => __('The image must not exceed 2MB.'),
        ];
    }

    protected function documentUploadRules(int $maxKb = 5120): array
    {
        return ['uploadedFile' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:' . $maxKb]];
    }

    protected function documentUploadMessages(): array
    {
        return [
            'uploadedFile.file' => __('The file must be a valid document.'),
            'uploadedFile.mimes' => __('The file must be PDF, DOC, DOCX, XLS, or XLSX.'),
            'uploadedFile.max' => __('The document must not exceed 5MB.'),
        ];
    }
}
```

Trait ini menyediakan **named validation rule sets** dengan **custom error messages**. Dipake di `updateAvatar()`:

```php
// Validate dengan custom messages
$this->validate($this->imageUploadRules(), $this->imageUploadMessages());
```

**Benefit custom messages:**
- User mendapat pesan error yang spesifik dan informatif
- E.g., "The image must not exceed 2MB." alih-alih generic "The uploadedFile failed to upload."
- Mendukung i18n via `__()` helper

### 4. Model Setup

**`app/Models/User.php`** — menambahkan Spatie Media capability:

```php
<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use InteractsWithMedia;
    // ... other traits ...

    // Daftarkan collection yang didukung
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()  // Only 1 file per collection. New file auto-deletes old one.
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    // Helper method untuk return avatar URL
    public function avatarUrl(): ?string
    {
        $url = $this->getFirstMediaUrl('avatar');
        return $url ?: null;
    }
}
```

**Penjelasan key methods:**

| Method | Fungsi |
|--------|--------|
| `->singleFile()` | Batasi collection ke 1 file. File baru otomatis hapus yang lama. |
| `->acceptsMimeTypes([...])` | Validasi di level library (defense in depth). |
| `$user->addMedia($file)->toMediaCollection('avatar')` | Store file ke collection 'avatar'. |
| `$user->clearMediaCollection('avatar')` | Hapus semua file di collection. |
| `$user->getFirstMedia('avatar')` | Get Media model object (atau null). |
| `$user->getFirstMediaUrl('avatar')` | Get full public URL (atau empty string). |

---

## 💻 Penggunaan

### Flux File-Upload Component (UI)

**`resources/views/flux/file-upload/index.blade.php`** sudah di-patch untuk support `wire:model`:

```blade
<flux:file-upload
    wire:model="uploadedFile"
    accept="image/jpeg,image/png,image/webp"
/>
```

Flux component ini:
- Render drag-drop UI
- Trigger file input
- Pass selected files ke Livewire via `wire:model="uploadedFile"`
- Alpine.js handles local preview via FileReader

### Volt Component Integration

Di file Volt component (e.g., `⚡profile.blade.php`):

```php
<?php
use App\Concerns\HasFileUpload;
use App\Concerns\FileUploadValidationRules;

new class extends Component {
    use HasFileUpload, FileUploadValidationRules;  // Add traits

    public function updateAvatar(): void
    {
        // 1. Validasi dengan custom messages
        $this->validate($this->imageUploadRules(), $this->imageUploadMessages());

        // 2. Guard: pastikan file ada
        if (! $this->uploadedFile) {
            Flux::toast(variant: 'warning', text: __('Please select an image first.'));
            return;
        }

        // 3. Upload via Spatie
        Auth::user()
            ->addMedia($this->uploadedFile)
            ->toMediaCollection('avatar');

        // 4. Refresh user state (untuk update avatar di UI)
        Auth::user()->refresh();

        // 5. Cleanup & feedback
        $this->uploadedFile = null;
        Flux::toast(variant: 'success', text: __('Avatar updated.'));
    }

    public function removeAvatar(): void
    {
        Auth::user()->clearMediaCollection('avatar');
        Flux::toast(variant: 'success', text: __('Avatar removed.'));
    }
};
?>

<blade-template>
    <flux:file-upload
        wire:model="uploadedFile"
        accept="image/jpeg,image/png,image/webp,image/gif"
    />

    @error('uploadedFile')
        <flux:text size="sm" class="!text-red-500">{{ $message }}</flux:text>
    @enderror

    <flux:button wire:click="updateAvatar">{{ __('Upload') }}</flux:button>
</blade-template>
```

### Display Avatar

Di nav menu atau anywhere:

```blade
<flux:avatar
    :src="auth()->user()->avatarUrl()"
    :name="auth()->user()->displayName()"
    :initials="auth()->user()->initials()"
/>
```

Flux avatar component automatically:
- Show image jika `src` provided
- Fallback ke initials jika `src` null

---

## 🏆 Contoh: Avatar User

Sudah fully implemented di `/settings/profile`. File-file terlibat:

1. **Model** — `app/Models/User.php`
   - `implements HasMedia`
   - `registerMediaCollections()` dengan avatar collection
   - `avatarUrl()` helper

2. **Volt Component** — `resources/views/pages/settings/⚡profile.blade.php`
   - `use HasFileUpload, FileUploadValidationRules`
   - `updateAvatar()` & `removeAvatar()` methods
   - Avatar section with file upload UI

3. **Navigation** — `desktop-user-menu.blade.php`, `desktop-profile-menu.blade.php`
   - Pass `:src="auth()->user()->avatarUrl()"` ke flux:avatar

4. **Testing** — `/settings/profile` page

---

## 🚀 Contoh: Feature Baru (Company Logo)

Untuk menambah upload file ke model baru (e.g., Company logo):

### Step 1: Update Model

**`app/Models/Company.php`**

```php
<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Company extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp']);
    }

    public function logoUrl(): ?string
    {
        $url = $this->getFirstMediaUrl('logo');
        return $url ?: null;
    }
}
```

### Step 2: Add File Upload in Volt Component

**`resources/views/pages/companies/⚡edit.blade.php`** (create if doesn't exist)

```php
<?php
use App\Concerns\HasFileUpload;
use App\Concerns\FileUploadValidationRules;
use App\Models\Company;

new class extends Component {
    use HasFileUpload, FileUploadValidationRules;

    public Company $company;

    public function save(): void
    {
        // ... normal validation ...

        // Handle logo upload (jika ada file yang diupload)
        if ($this->uploadedFile) {
            $this->validate($this->imageUploadRules(maxKb: 4096));  // Max 4MB for logos
            
            $this->company
                ->addMedia($this->uploadedFile)
                ->toMediaCollection('logo');
            
            $this->uploadedFile = null;
        }

        // ... save company data ...
        Flux::toast(variant: 'success', text: __('Company updated.'));
    }
};
?>

<blade-template>
    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="company.name" label="Company Name" />

        <div>
            <flux:heading size="sm">{{ __('Company Logo') }}</flux:heading>
            <flux:file-upload wire:model="uploadedFile" accept="image/*" />
            @error('uploadedFile') <flux:text size="sm" class="text-red-500">{{ $message }}</flux:text> @enderror
        </div>

        <flux:button type="submit">{{ __('Save') }}</flux:button>
    </form>
</blade-template>
```

### Step 3: Display Logo

```blade
<img :src="$company->logoUrl()" alt="{{ $company->name }}" class="h-16" />

<!-- Or using Flux avatar as placeholder -->
<flux:avatar :src="$company->logoUrl()" :name="$company->name" />
```

**That's it!** Tidak perlu migration, tidak perlu ubah service, tidak perlu ubah trait — cukup:
1. Add `HasMedia` interface & `InteractsWithMedia` trait ke model
2. Define `registerMediaCollections()` dengan collection name
3. Call `$model->addMedia($file)->toMediaCollection('name')` di component

---

## 🐛 Troubleshooting

### Error: "Trait not found"

**Solusi:**
```bash
composer dump-autoload -q
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### File upload null saat click Upload

**Penyebab:** File belum selesai upload ke server sebelum button diklik.

**Solusi:** Validasi set ke `required` (bukan `nullable`), akan error jika file null.

### File not storing / directory permission error

**Penyebab:** `public/media` folder belum ada atau permission denied.

**Solusi:**
```bash
# Ensure directory exists
mkdir -p public/media

# Fix permissions
chmod -R 755 public/media
chmod -R 755 storage/app/public
```

### Avatar not showing in nav menu

**Penyebab:** `avatarUrl()` return null, atau image path salah, atau APP_URL mismatch.

**Solusi:** Cek di database `media` table:
```sql
SELECT * FROM media WHERE model_type = 'App\\Models\\User' AND model_id = 1;
```

Jika kosong, file belum terupload. Cek browser console untuk Livewire upload errors.

**Jika file ada di DB tapi avatar tidak muncul:**
- Pastikan `APP_URL` di `.env` sesuai dengan URL yang Anda akses
- Contoh: Jika akses via `http://localhost:8000`, set `APP_URL=http://localhost:8000`
- Jika mismatch, image URL akan invalid (e.g., pointing ke `https://example.test` saat akses `http://localhost`)
- Setelah fix, jalankan `php artisan config:clear`

### Error messages tidak informatif

**Penyebab:** Validasi error menggunakan default Laravel messages, bukan custom messages.

**Solusi:** Pastikan `updateAvatar()` pass custom messages:

```php
// ✅ Benar
$this->validate($this->imageUploadRules(), $this->imageUploadMessages());

// ❌ Salah (generic error messages)
$this->validate($this->imageUploadRules());
```

Custom messages memberikan user feedback yang jelas, e.g.:
- "The image must not exceed 2MB." (alih-alih generic error)
- "The file must be JPG, PNG, WebP, or GIF."

### Max execution time exceeded

**Penyebab:** File terlalu besar atau server timeout.

**Solusi:** Increase di `php.ini` atau batasi size di validation:
```php
'uploadedFile' => ['required', 'image', 'max:2048'],  // 2MB max
```

---

## 📚 Referensi

- [Spatie Media Library Docs](https://spatie.be/docs/laravel-medialibrary/v11/basic-usage/preparing-your-model)
- [Laravel File Storage](https://laravel.com/docs/13/filesystem)
- [Livewire File Uploads](https://livewire.laravel.com/docs/uploads)
- [Flux File Upload Component](https://fluxui.dev/docs)

---

## 📝 Changelog

### v2.0 (2026-05-29)
- ✨ Added custom error messages system (`imageUploadMessages()`, `documentUploadMessages()`)
- 🔧 Added `Auth::user()->refresh()` to ensure avatar updates immediately in UI
- 📋 Added APP_URL configuration guidance to prevent image loading issues
- 📖 Enhanced troubleshooting section with APP_URL mismatch and error message guidance

### v1.0 (2026-05-29)
- Initial implementation with Spatie Media Library
- Avatar upload system at `/settings/profile`
- Base traits: `HasFileUpload`, `FileUploadValidationRules`

---

**Last Updated:** 2026-05-29  
**Author:** Development Team  
**Status:** ✅ Production Ready

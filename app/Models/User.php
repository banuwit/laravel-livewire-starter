<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable(['email', 'password', 'is_active', 'name', 'phonenumber', 'gender', 'organization_id', 'branch_id', 'created_by', 'updated_by'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, LogsActivity, InteractsWithMedia, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $userId = self::getDefaultUserId();
            if ($userId) {
                $model->created_by = $userId;
                $model->updated_by = $userId;
            }
        });

        static::updating(function (self $model) {
            $userId = self::getDefaultUserId();
            if ($userId) {
                $model->updated_by = $userId;
            }
        });

        static::deleting(function (self $model) {
            if ($model->isForceDeleting()) {
                return;
            }
            $userId = self::getDefaultUserId();
            if ($userId) {
                $model->deleted_by = $userId;
                $model->saveQuietly();
            }
        });
    }

    protected static function getDefaultUserId(): ?int
    {
        if (auth()->check()) {
            return auth()->id();
        }

        return self::whereHas('roles', fn ($q) => $q->where('name', 'superadmin'))
            ->first()?->id ?? self::first()?->id;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'gender' => 'string',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'is_active', 'organization_id', 'branch_id', 'gender'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('model')
            ->setDescriptionForEvent(fn (string $e) => "User {$e}");
    }

    public function displayName(): string
    {
        return $this->name ?? $this->email;
    }

    public function initials(): string
    {
        return Str::of($this->displayName())
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function avatarUrl(): ?string
    {
        $url = $this->getFirstMediaUrl('avatar');
        return $url ?: null;
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

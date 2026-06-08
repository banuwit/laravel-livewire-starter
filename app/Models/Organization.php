<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable(['name', 'code', 'phone', 'email', 'address', 'is_active', 'created_by', 'updated_by'])]
class Organization extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

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

        return User::whereHas('roles', fn ($q) => $q->where('name', 'superadmin'))
            ->first()?->id ?? User::first()?->id;
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('model')
            ->setDescriptionForEvent(fn (string $e) => "Organization {$e}");
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'icon', 'route_name', 'route_pattern',
        'parent_id', 'level', 'sort_order', 'layout', 'is_active',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

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

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class)->orderBy('sort_order');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function scopeRoots($query)
    {
        return $query->where('level', 0)->orderBy('sort_order');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

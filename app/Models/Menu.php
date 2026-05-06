<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'name', 'slug', 'icon', 'route_name', 'route_pattern',
        'parent_id', 'level', 'sort_order', 'layout', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

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
}

<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends SpatiePermission
{
    protected $fillable = ['name', 'guard_name', 'menu_id', 'sort_order'];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}

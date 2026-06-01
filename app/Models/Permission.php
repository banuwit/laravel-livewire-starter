<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Permission extends SpatiePermission
{
    use LogsActivity;

    protected $fillable = ['name', 'guard_name', 'menu_id', 'sort_order'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'menu_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('model')
            ->setDescriptionForEvent(fn (string $e) => "Permission {$e}");
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}

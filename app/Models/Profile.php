<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id', 'identity_number',
    'religion_id', 'birth_date', 'marital_status_id', 'address',
    'country_id', 'province_id', 'city_id',
    'created_by', 'updated_by', 'deleted_by',
])]
class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'profiles';

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
        return [
            'birth_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function religion()
    {
        return $this->belongsTo(Parameter::class, 'religion_id');
    }

    public function maritalStatus()
    {
        return $this->belongsTo(Parameter::class, 'marital_status_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
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

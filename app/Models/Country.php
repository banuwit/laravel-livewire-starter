<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    /** @use HasFactory<\Database\Factories\CountryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    public function provinces()
    {
        return $this->hasMany(Province::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}

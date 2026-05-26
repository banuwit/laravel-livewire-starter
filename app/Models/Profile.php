<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'identity_number',
    'religion_id', 'birth_date', 'marital_status_id', 'address',
    'country_id', 'province_id', 'city_id',
])]
class Profile extends Model
{
    use HasFactory;

    protected $table = 'profiles';

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
}

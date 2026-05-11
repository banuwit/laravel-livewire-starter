<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'code', 'phone', 'email', 'address', 'is_active'])]
class Company extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}

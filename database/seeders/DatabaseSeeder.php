<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\Province;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesPermissionsSeeder::class,
            MenuSeeder::class,
            CountrySeeder::class,
            ProvinceSeeder::class,
            CitySeeder::class,
        ]);

        $country = Country::first();
        $province = Province::first();
        $city = City::first();

        // Seed 1 company + branches
        $company = Company::create([
            'name' => 'PT Lite Indonesia',
            'code' => 'LITE',
            'phone' => '(021) 123-4567',
            'email' => 'info@lite.id',
            'address' => 'Jl. Sudirman No. 1, Jakarta Pusat',
            'is_active' => true,
        ]);

        $branches = collect([
            ['name' => 'Head Office', 'code' => 'HO', 'address' => 'Jl. Sudirman No. 1, Jakarta Pusat'],
            ['name' => 'Branch Surabaya', 'code' => 'SBY', 'address' => 'Jl. Pemuda No. 15, Surabaya'],
            ['name' => 'Branch Bandung', 'code' => 'BDG', 'address' => 'Jl. Asia Afrika No. 8, Bandung'],
        ])->map(fn ($b) => $company->branches()->create(array_merge($b, ['is_active' => true])));

        $superadmin = User::factory()->create([
            'username' => 'banulite',
            'email' => 'banu@lite.id',
            'password' => 'testtest',
            'is_active' => true,
        ]);
        $superadmin->employee()->create([
            'company_id' => $company->id,
            'branch_id' => $branches->first()->id,
            'name' => 'Banu Lite',
            'gender' => 'male',
            'phonenumber' => '08123456789',
            'religion' => 'islam',
            'country_id' => $country?->id ?? 1,
            'province_id' => $province?->id ?? 1,
            'city_id' => $city?->id ?? 1,
            'is_active' => true,
            'employee_type' => 'permanent',
            'join_date' => '2020-01-01',
        ]);
        $superadmin->assignRole('superadmin');

        User::factory(36)
            ->withEmployee(['company_id' => $company->id, 'branch_id' => $branches->random()->id])
            ->create()
            ->each(fn ($user) => $user->assignRole('staff'));
    }
}

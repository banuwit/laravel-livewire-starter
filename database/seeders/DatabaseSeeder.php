<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\Parameter;
use App\Models\Province;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ParameterSeeder::class,
            RolesPermissionsSeeder::class,
            MenuSeeder::class,
            LocationSeeder::class,
        ]);

        $country = Country::where('code', 'ID')->first();
        $province = Province::where('name', 'Jawa Barat')->first();
        $city = City::where('name', 'Bandung')->first();

        $company = Company::create([
            'name' => 'WIT. Indonesia',
            'code' => 'WIT',
            'phone' => '(022) 123-4567',
            'email' => 'information@wit.id',
            'address' => 'Jl. Sukakarya II No.40',
            'is_active' => true,
        ]);

        $branches = collect([
            ['name' => 'Head Office', 'type' => 'headquarter', 'code' => 'HO', 'address' => 'Jl. Sudirman No. 1, Jakarta Pusat'],
            ['name' => 'Branch Surabaya', 'type' => 'branch', 'code' => 'SBY', 'address' => 'Jl. Pemuda No. 15, Surabaya'],
            ['name' => 'Branch Bandung', 'type' => 'branch', 'code' => 'BDG', 'address' => 'Jl. Asia Afrika No. 8, Bandung'],
        ])->map(fn ($b) => $company->branches()->create(array_merge($b, ['is_active' => true])));

        $superadmin = User::factory()->create([
            'name' => 'Project Admin',
            'email' => 'projectadmin@wit.id',
            'password' => 'demoadmin123*#',
            'phonenumber' => '08123456789',
            'gender' => 'male',
            'company_id' => $company->id,
            'branch_id' => $branches->first()->id,
            'is_active' => true,
        ]);
        $superadmin->profile()->create([
            'religion_id' => Parameter::where('code', 'islam')->value('id'),
            'country_id' => $country->id,
            'province_id' => $province->id,
            'city_id' => $city->id,
        ]);
        $superadmin->assignRole('superadmin');

        User::factory(36)
            ->withProfile()
            ->state(['company_id' => $company->id, 'branch_id' => $branches->random()->id])
            ->create()
            ->each(fn ($user) => $user->assignRole('staff'));
    }
}

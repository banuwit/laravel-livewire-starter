<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Country;
use App\Models\Province;
use App\Models\City;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesPermissionsSeeder::class,
            MenuSeeder::class,
            CountrySeeder::class,
            ProvinceSeeder::class,
            CitySeeder::class,
        ]);

        // Get existing country, province, city ids
        $country = Country::first();
        $province = Province::first();
        $city = City::first();

        User::factory()->create([
            'name' => 'Banu Lite',
            'email' => 'banu@lite.id',
            'password' => 'testtest',
            'gender' => 'male',
            'phonenumber' => '08123456789',
            'religion' => 'islam',
            'is_active' => 'active',
            'country_id' => $country?->id ?? 1,
            'province_id' => $province?->id ?? 1,
            'city_id' => $city?->id ?? 1,
        ])->assignRole('superadmin');

        User::factory(36)->create()
            ->each(function ($user) {
                $user->assignRole('staff');
            });
    }
}

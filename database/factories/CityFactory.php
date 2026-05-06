<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Province;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city,
            'code' => null,
            'province_id' => null,
            'country_id' => null,
        ];
    }
}

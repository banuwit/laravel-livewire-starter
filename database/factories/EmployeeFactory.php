<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        $province = Province::inRandomOrder()->first();

        return [
            'user_id' => null,
            'company_id' => fn () => Company::inRandomOrder()->value('id'),
            'branch_id' => null,
            'name' => fake()->name(),
            'gender' => fake()->randomElement(['male', 'female']),
            'phonenumber' => fake()->phoneNumber(),
            'religion' => fake()->randomElement(['islam', 'kristen', 'hindu', 'buddhist', 'other']),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->dateTimeBetween('-55 years', '-20 years')->format('Y-m-d'),
            'marital_status' => fake()->randomElement(['single', 'married', 'divorced', 'widowed']),
            'address' => fake()->address(),
            'country_id' => $province?->country_id,
            'province_id' => $province?->id,
            'city_id' => $province
                ? \App\Models\City::where('province_id', $province->id)->inRandomOrder()->value('id')
                : null,
            'is_active' => true,
            'employee_type' => fake()->randomElement(['permanent', 'contract', 'intern', 'parttime']),
            'join_date' => fake()->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'end_date' => null,
        ];
    }
}

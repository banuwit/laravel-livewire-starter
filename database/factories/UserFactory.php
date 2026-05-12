<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => true,
            'phonenumber' => fake()->phoneNumber(),
            'gender' => fake()->randomElement(['male', 'female']),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function withEmployee(array $attributes = []): static
    {
        return $this->afterCreating(function (User $user) use ($attributes) {
            $province = \App\Models\Province::inRandomOrder()->first();

            $user->employee()->create(array_merge([
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
                'employee_type' => fake()->randomElement(['permanent', 'contract', 'intern', 'parttime']),
                'join_date' => fake()->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
                'end_date' => null,
            ], $attributes));
        });
    }
}

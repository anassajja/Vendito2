<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->unique()->firstName, // Generate a unique first name
            'last_name' => $this->faker->unique()->lastName, // Generate a unique last name
            'username' => $this->faker->unique()->userName, // Generate a unique username
            'cnie' => $this->faker->unique()->randomNumber(8), // Generate a unique CNIE
            'phone' => $this->faker->unique()->phoneNumber, // Generate a unique phone number
            'address' => $this->faker->address, // Generate a random address
            'city' => $this->faker->city, // Generate a random city
            'avatar' => $this->faker->imageUrl(640, 480, 'people', true), // Generate a random avatar
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make($this->faker->password), // Hashed password
            'role' => 'user',
            'status' => 'active',
            'is_admin' => false,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return $this
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'avatar' => 'avatar-'.fake()->numberBetween(1, 8).'.jpg',
            'address' => fake()->address(),
            'rating' => fake()->randomFloat(1, 3.5, 5),
            'wallet_balance' => fake()->randomFloat(2, 100, 9000),
            'joining_date' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\ChatContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatContact>
 */
class ChatContactFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'avatar' => fake()->randomElement([
                'assets/images/users/avatar-'.fake()->numberBetween(1, 8).'.jpg',
                null,
            ]),
            'status' => fake()->randomElement(['online', 'away', 'offline']),
            'last_message' => fake()->sentence(4),
            'unread' => fake()->numberBetween(0, 6),
            'user_id' => null,
        ];
    }
}

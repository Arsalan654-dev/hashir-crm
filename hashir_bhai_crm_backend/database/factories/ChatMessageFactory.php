<?php

namespace Database\Factories;

use App\Models\ChatContact;
use App\Models\ChatMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_contact_id' => ChatContact::factory(),
            'body' => fake()->sentence(10),
            'is_outgoing' => fake()->boolean(),
            'day_label' => fake()->randomElement(['Today', 'Yesterday', null]),
            'sent_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }
}

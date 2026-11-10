<?php

namespace Database\Factories;

use App\Models\EmailMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailMessage>
 */
class EmailMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'from_name' => $name,
            'from_email' => fake()->safeEmail(),
            'to_email' => fake()->safeEmail(),
            'subject' => fake()->sentence(5),
            'body' => fake()->paragraphs(3, true),
            'teaser' => fake()->sentence(8),
            'avatar' => 'assets/images/users/avatar-'.fake()->numberBetween(1, 8).'.jpg',
            'folder' => fake()->randomElement(['inbox', 'sent', 'draft', 'starred', 'important', 'trash']),
            'threads' => fake()->numberBetween(1, 30).' Threads',
            'time_label' => fake()->time('g:i A'),
            'date_label' => fake()->randomElement(['Today', 'Yesterday', fake()->date('d M, Y')]),
            'is_read' => fake()->boolean(60),
            'is_starred' => fake()->boolean(20),
            'is_important' => fake()->boolean(20),
            'received_at' => fake()->dateTimeBetween('-14 days', 'now'),
        ];
    }
}

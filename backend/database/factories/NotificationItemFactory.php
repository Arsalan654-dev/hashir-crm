<?php

namespace Database\Factories;

use App\Models\NotificationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationItem>
 */
class NotificationItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $withAvatar = fake()->boolean(50);

        return [
            'title' => fake()->randomElement([
                'James Lemire', 'Your order is placed', 'Your item is shipped',
                'Salena Layfield', 'New comment on task', 'Payment received',
            ]),
            'body' => fake()->sentence(8),
            'avatar' => $withAvatar ? 'assets/images/users/avatar-'.fake()->numberBetween(1, 8).'.jpg' : null,
            'icon' => $withAvatar ? null : fake()->randomElement(['bx bx-cart', 'bx bx-badge-check', 'bx bx-message']),
            'icon_bg' => $withAvatar ? null : fake()->randomElement(['bg-primary', 'bg-success', 'bg-info']),
            'time_label' => fake()->randomElement(['1 hour ago', '3 min ago', '8 min ago', '2 hours ago']),
            'is_read' => fake()->boolean(30),
            'user_id' => null,
        ];
    }
}

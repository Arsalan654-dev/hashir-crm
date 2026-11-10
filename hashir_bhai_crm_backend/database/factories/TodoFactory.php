<?php

namespace Database\Factories;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Todo>
 */
class TodoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['Waiting', 'Approved', 'Completed']);
        $completed = $status === 'Completed' || fake()->boolean(30);

        return [
            'title' => fake()->sentence(4),
            'team' => fake()->randomElement([
                'Product Design', 'Development', 'Illustration',
                'Design', 'Angular', 'Backend',
            ]),
            'status' => $status,
            'is_completed' => $completed,
            'comments' => fake()->numberBetween(1, 12),
            'members' => collect(range(1, fake()->numberBetween(1, 3)))->map(fn () => [
                'avatar' => fake()->randomElement([null, 'assets/images/users/avatar-'.fake()->numberBetween(1, 8).'.jpg']),
                'letter' => fake()->randomElement(['A', 'R', 'V', 'S']),
            ])->all(),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'user_id' => User::factory(),
        ];
    }
}

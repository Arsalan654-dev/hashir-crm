<?php

namespace Database\Factories;

use App\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 month', '+2 months');
        $hasEnd = fake()->boolean(40);

        return [
            'title' => fake()->randomElement([
                'All Day Event', 'Long Event', 'Repeating Event',
                'Meeting', 'Lunch', 'Birthday Party', 'Conference',
            ]),
            'start_date' => $start,
            'end_date' => $hasEnd ? fake()->dateTimeBetween($start, '+5 days') : null,
            'class_name' => fake()->randomElement([
                'bg-primary', 'bg-success', 'bg-danger', 'bg-info', 'bg-warning', 'bg-dark',
            ]),
            'all_day' => fake()->boolean(70),
            'description' => fake()->sentence(6),
            'user_id' => null,
        ];
    }
}

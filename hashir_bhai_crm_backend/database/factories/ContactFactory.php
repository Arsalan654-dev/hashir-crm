<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $positions = [
            ['Full Stack Developer', 'bg-success-subtle', 'text-success'],
            ['Frontend Developer', 'bg-info-subtle', 'text-info'],
            ['UI/UX Designer', 'bg-danger-subtle', 'text-danger'],
            ['Backend Developer', 'bg-primary-subtle', 'text-primary'],
        ];
        $position = fake()->randomElement($positions);

        return [
            'name' => fake()->name(),
            'position' => $position[0],
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'avatar' => 'assets/images/users/avatar-'.fake()->numberBetween(1, 8).'.jpg',
            'badge_class' => $position[1],
            'badge_text_class' => $position[2],
            'projects' => fake()->numberBetween(100, 250),
            'bio' => fake()->sentence(12),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'zip' => fake()->postcode(),
            'company' => fake()->company(),
        ];
    }
}

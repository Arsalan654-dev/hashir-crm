<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'business_type' => fake()->randomElement(Organization::BUSINESS_TYPES),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'whatsapp' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'timezone' => 'Asia/Dubai',
            'currency' => 'USD',
            'status' => 'active',
            'payment_status' => 'unpaid',
            'plan_name' => 'Standard',
            'plan_amount' => 100,
            'billing_cycle' => 'monthly',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'payment_status' => 'paid',
            'paid_until' => now()->addMonth(),
        ]);
    }

    public function crmBlocked(): static
    {
        return $this->state(fn (array $attributes): array => [
            'crm_blocked' => true,
        ]);
    }
}

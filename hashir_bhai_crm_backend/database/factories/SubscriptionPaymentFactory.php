<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\SubscriptionPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPayment>
 */
class SubscriptionPaymentFactory extends Factory
{
    protected $model = SubscriptionPayment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'amount' => 100,
            'currency' => 'USD',
            'method' => 'bank_transfer',
            'status' => 'paid',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->addMonth()->toDateString(),
            'paid_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }
}

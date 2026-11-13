<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_no' => '#INV-'.fake()->unique()->numerify('0###'),
            'customer_id' => Customer::factory(),
            'billing_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'invoice_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'status' => fake()->randomElement(['Paid', 'Pending']),
            'amount' => fake()->randomFloat(2, 10, 500),
            'payment_method' => fake()->randomElement(['Credit / Debit Card', 'Paypal', 'Bank Transfer']),
            'description' => fake()->sentence(6),
            'tax' => fake()->randomFloat(2, 0, 50),
            'discount' => fake()->randomFloat(2, 0, 20),
            'shipping' => fake()->randomFloat(2, 0, 30),
        ];
    }
}

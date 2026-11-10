<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => '#DS'.fake()->unique()->numerify('0###'),
            'customer_id' => Customer::factory(),
            'product_id' => null,
            'billing_name' => fake()->name(),
            'order_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'total' => fake()->randomFloat(2, 10, 500),
            'payment_status' => fake()->randomElement(['Paid', 'Pending', 'Chargeback', 'Refund']),
            'payment_method' => fake()->randomElement(['Mastercard', 'Visa', 'Paypal', 'COD']),
            'order_status' => fake()->randomElement(['Processing', 'Shipped', 'Delivered', 'Cancelled']),
        ];
    }
}

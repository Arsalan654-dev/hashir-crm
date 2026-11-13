<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 50, 500);

        return [
            'name' => fake()->words(3, true),
            'sku' => 'SKU-'.fake()->unique()->numerify('####'),
            'category_id' => null,
            'color' => fake()->randomElement(['Gray', 'Black', 'White', 'Blue']),
            'tags' => fake()->randomElement(['Chair', 'Table', 'Furniture', 'Office']),
            'price' => $price,
            'old_price' => $price + fake()->randomFloat(2, 10, 80),
            'image' => 'assets/images/product/img-'.fake()->numberBetween(1, 8).'.png',
            'rating' => fake()->randomFloat(1, 3, 5),
            'stock' => fake()->numberBetween(0, 100),
            'badge' => fake()->randomElement(['New', '- 20 %', null, null]),
            'badge_class' => 'bg-danger',
            'description' => fake()->sentence(10),
            'is_featured' => fake()->boolean(30),
        ];
    }
}

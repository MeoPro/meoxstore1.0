<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stock = fake()->numberBetween(0, 100);

        return [
            'product_id' => Product::factory(),
            'name' => 'Default',
            'price' => fake()->randomFloat(2, 0, 100),
            'stock' => $stock,
            'minimum_quantity' => 1,
            'maximum_quantity' => $stock,
            'status' => 'active',
            'sort_order' => 0,
        ];
    }
}

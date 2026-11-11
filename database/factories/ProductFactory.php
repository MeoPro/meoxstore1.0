<?php

namespace Database\Factories;

use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_category_id' => GameCategory::factory(),
            'item_category_id' => ItemCategory::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'image' => null,
            'status' => 'active',
            'is_featured' => false,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product): void {
            $product->gameCategory()
                ->firstOrFail()
                ->itemCategories()
                ->syncWithoutDetaching($product->item_category_id);

            if (! $product->variants()->exists()) {
                ProductVariant::factory()->for($product)->create();
            }
        });
    }
}

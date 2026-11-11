<?php

namespace Database\Seeders;

use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_admin' => true,
        ]);

        $gameCategories = collect(['Blox Fruit', 'Fisch', 'Roblox', 'Growtopia'])
            ->mapWithKeys(fn (string $name): array => [
                $name => GameCategory::firstOrCreate(['name' => $name], ['status' => 'active']),
            ]);

        $itemCategories = collect(['Account', 'Item', 'Skin', 'Joki'])
            ->mapWithKeys(fn (string $name): array => [
                $name => ItemCategory::firstOrCreate(['name' => $name], ['status' => 'active']),
            ]);

        $products = [
            ['name' => 'Blox Fruit', 'game' => 'Blox Fruit', 'item' => 'Item', 'variants' => ['Default', '2x Fruit', '5x Fruit']],
            ['name' => 'Fisch', 'game' => 'Fisch', 'item' => 'Item', 'variants' => ['Default']],
            ['name' => 'Roblox Account', 'game' => 'Roblox', 'item' => 'Account', 'variants' => ['Default']],
            ['name' => 'Growtopia Item', 'game' => 'Growtopia', 'item' => 'Item', 'variants' => ['Default']],
        ];

        foreach ($products as $productData) {
            $gameCategory = $gameCategories[$productData['game']];
            $itemCategory = $itemCategories[$productData['item']];
            $gameCategory->itemCategories()->syncWithoutDetaching($itemCategory->id);

            $product = Product::firstOrCreate(
                [
                    'name' => $productData['name'],
                    'game_category_id' => $gameCategory->id,
                    'item_category_id' => $itemCategory->id,
                ],
                ['status' => 'active', 'is_featured' => false],
            );

            foreach ($productData['variants'] as $sortOrder => $variantName) {
                ProductVariant::firstOrCreate(
                    ['product_id' => $product->id, 'name' => $variantName],
                    [
                        'price' => 0,
                        'stock' => 0,
                        'minimum_quantity' => 1,
                        'maximum_quantity' => 0,
                        'status' => 'active',
                        'sort_order' => $sortOrder,
                    ],
                );
            }
        }
    }
}

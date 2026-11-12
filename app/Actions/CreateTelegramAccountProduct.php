<?php

namespace App\Actions;

use App\Models\GameCategory;
use App\Models\ItemCategory;
use App\Models\Product;
use App\Models\TelegramAccountDraft;
use Illuminate\Support\Facades\DB;
use LogicException;

class CreateTelegramAccountProduct
{
    public function execute(TelegramAccountDraft $draft): Product
    {
        return DB::transaction(function () use ($draft): Product {
            $data = $draft->data;
            $images = $draft->images;
            $gameCategory = GameCategory::query()->firstOrCreate(
                ['name' => 'Blox Fruit'],
                ['status' => 'active'],
            );
            $itemCategory = ItemCategory::query()->firstOrCreate(
                ['name' => 'Account'],
                ['status' => 'active'],
            );

            if ($gameCategory->status !== 'active' || $itemCategory->status !== 'active') {
                throw new LogicException('Activate the Blox Fruit and Account categories before publishing.');
            }

            $gameCategory->itemCategories()->syncWithoutDetaching([$itemCategory->id]);

            $product = Product::query()->create([
                'game_category_id' => $gameCategory->id,
                'item_category_id' => $itemCategory->id,
                'name' => $data['website_title'] ?? 'Blox Fruit Account - '.$data['username'],
                'description' => $data['description'],
                'image' => $images[0] ?? null,
                'images' => $images,
                'purchase_fields' => [],
                'status' => 'active',
                'is_account_listing' => true,
            ]);

            $product->variants()->create([
                'name' => '1 Account',
                'price' => $data['price_idr'],
                'stock' => 1,
                'minimum_quantity' => 1,
                'maximum_quantity' => 1,
                'status' => 'active',
                'sort_order' => 0,
            ]);

            $draft->update([
                'status' => 'publishing',
                'product_id' => $product->id,
            ]);

            return $product;
        });
    }
}

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
            if (isset($data['game_category_id'])) {
                $gameCategoryId = filter_var($data['game_category_id'], FILTER_VALIDATE_INT);
                $gameCategory = $gameCategoryId === false
                    ? null
                    : GameCategory::query()->find($gameCategoryId);
            } else {
                $gameCategory = GameCategory::query()->firstOrCreate(
                    ['name' => 'Blox Fruit'],
                    ['status' => 'active'],
                );
            }
            $itemCategory = ItemCategory::query()->firstOrCreate(
                ['name' => 'Account'],
                ['status' => 'active'],
            );

            if ($gameCategory === null) {
                throw new LogicException('The selected game category no longer exists.');
            }

            if ($gameCategory->status !== 'active' || $itemCategory->status !== 'active') {
                throw new LogicException('Activate the selected game and Account categories before publishing.');
            }

            $gameCategory->itemCategories()->syncWithoutDetaching([$itemCategory->id]);

            $product = Product::query()->create([
                'game_category_id' => $gameCategory->id,
                'item_category_id' => $itemCategory->id,
                'name' => $data['website_title'] ?? $gameCategory->name.' Account - '.$data['username'],
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

<?php

namespace App\Models;

use Database\Factories\GameCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameCategory extends Model
{
    /** @use HasFactory<GameCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'status',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function itemCategories(): BelongsToMany
    {
        return $this->belongsToMany(ItemCategory::class, 'game_category_item_category');
    }
}

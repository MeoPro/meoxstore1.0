<?php

namespace App\Models;

use Database\Factories\GameCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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

    protected static function booted(): void
    {
        static::saving(function (self $gameCategory): void {
            if (! $gameCategory->isDirty('name') && filled($gameCategory->slug)) {
                return;
            }

            $baseSlug = Str::slug($gameCategory->name) ?: 'game';
            if (ctype_digit($baseSlug)) {
                $baseSlug = 'game-'.$baseSlug;
            }

            $slug = $baseSlug;
            $suffix = 2;
            while (self::query()
                ->where('slug', $slug)
                ->when($gameCategory->exists, fn ($query) => $query->where('id', '!=', $gameCategory->getKey()))
                ->exists()) {
                $slug = $baseSlug.'-'.$suffix;
                $suffix++;
            }

            $gameCategory->slug = $slug;
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function itemCategories(): BelongsToMany
    {
        return $this->belongsToMany(ItemCategory::class, 'game_category_item_category');
    }
}

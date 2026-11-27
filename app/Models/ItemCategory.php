<?php

namespace App\Models;

use Database\Factories\ItemCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ItemCategory extends Model
{
    public const BOBUX_TYPES = ['gift_ingame', 'gamepass_5_days', 'login'];

    /** @use HasFactory<ItemCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'bobux_type',
        'uses_robux',
        'image',
        'status',
    ];

    protected $attributes = [
        'bobux_type' => null,
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'uses_robux' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $itemCategory): void {
            if (! $itemCategory->isDirty('name') && filled($itemCategory->slug)) {
                return;
            }

            $baseSlug = Str::slug($itemCategory->name) ?: 'category';
            if (ctype_digit($baseSlug)) {
                $baseSlug = 'category-'.$baseSlug;
            }

            $slug = $baseSlug;
            $suffix = 2;
            while (self::query()
                ->where('slug', $slug)
                ->when($itemCategory->exists, fn ($query) => $query->where('id', '!=', $itemCategory->getKey()))
                ->exists()) {
                $slug = $baseSlug.'-'.$suffix;
                $suffix++;
            }

            $itemCategory->slug = $slug;
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function subcategoryProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'item_subcategory_product',
            'item_category_id',
            'product_id',
        );
    }

    public function usesBobux(): bool
    {
        return $this->bobux_type !== null || (bool) $this->uses_robux;
    }

    public function usesBobuxRate(): bool
    {
        return in_array($this->bobux_type ?? ($this->uses_robux ? 'gift_ingame' : null), [
            'gift_ingame',
            'gamepass_5_days',
        ], true);
    }

    public function localizedName(): string
    {
        $translationKey = 'item_categories.'.Str::slug($this->name);
        $localizedName = __($translationKey);

        return $localizedName === $translationKey ? $this->name : $localizedName;
    }

    public function gameCategories(): BelongsToMany
    {
        return $this->belongsToMany(GameCategory::class, 'game_category_item_category');
    }
}

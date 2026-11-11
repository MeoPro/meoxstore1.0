<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    public const BADGE_OPTIONS = ['promo', 'trending', 'hot', 'discount'];

    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'game_category_id',
        'item_category_id',
        'name',
        'description',
        'badges',
        'discount_percent',
        'image',
        'images',
        'purchase_fields',
        'status',
        'is_featured',
        'is_account_listing',
    ];

    protected $attributes = [
        'status' => 'active',
        'is_featured' => false,
        'is_account_listing' => false,
        'badges' => '[]',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_account_listing' => 'boolean',
            'badges' => 'array',
            'images' => 'array',
            'purchase_fields' => 'array',
            'discount_percent' => 'decimal:2',
        ];
    }

    public function gameCategory(): BelongsTo
    {
        return $this->belongsTo(GameCategory::class);
    }

    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function priceInIdr(float $price, ProductCurrencyRate $currencyRates): float
    {
        $priceInIdr = match ($this->bobuxType()) {
            'gift_ingame' => $price * (float) $currencyRates->idr_per_robux,
            'gamepass_5_days' => $price * (float) $currencyRates->idr_per_gamepass_bobux,
            default => $price,
        };

        return $priceInIdr * (1 - ((float) ($this->discount_percent ?? 0) / 100));
    }

    public function priceBeforeDiscountInIdr(float $price, ProductCurrencyRate $currencyRates): float
    {
        return match ($this->bobuxType()) {
            'gift_ingame' => $price * (float) $currencyRates->idr_per_robux,
            'gamepass_5_days' => $price * (float) $currencyRates->idr_per_gamepass_bobux,
            default => $price,
        };
    }

    public function priceInUsd(float $price, ProductCurrencyRate $currencyRates): float
    {
        $priceInIdr = $this->priceInIdr($price, $currencyRates);

        if ($this->is_account_listing) {
            $priceInIdr += 10_000;
        }

        return $priceInIdr / (float) $currencyRates->idr_per_usd;
    }

    public function priceBeforeDiscountInUsd(float $price, ProductCurrencyRate $currencyRates): float
    {
        $priceInIdr = $this->priceBeforeDiscountInIdr($price, $currencyRates);

        if ($this->is_account_listing) {
            $priceInIdr += 10_000;
        }

        return $priceInIdr / (float) $currencyRates->idr_per_usd;
    }

    /**
     * @return array<int, string>
     */
    public function galleryImages(): array
    {
        $images = array_values(array_filter($this->images ?? [], fn ($image) => is_string($image) && $image !== ''));

        if ($images === [] && $this->image !== null) {
            return [$this->image];
        }

        return $images;
    }

    public function hasDiscount(): bool
    {
        return (float) ($this->discount_percent ?? 0) > 0;
    }

    public function usesRobuxPricing(): bool
    {
        return $this->itemCategory?->usesBobux() ?? false;
    }

    public function usesBobuxRate(): bool
    {
        return $this->itemCategory?->usesBobuxRate() ?? false;
    }

    public function bobuxType(): ?string
    {
        return $this->itemCategory?->bobux_type
            ?? ($this->itemCategory?->uses_robux ? 'gift_ingame' : null);
    }

    public function bobuxAmount(float $price, ?float $manualAmount = null): ?float
    {
        return match ($this->bobuxType()) {
            'gift_ingame', 'gamepass_5_days' => $price,
            'login' => $manualAmount,
            default => null,
        };
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function purchaseDetailsType(): ?string
    {
        $gameName = Str::lower($this->gameCategory?->name ?? '');

        if (Str::contains($gameName, 'roblox')) {
            return 'roblox';
        }

        if (Str::contains($gameName, 'blox fruit')) {
            return 'blox_fruit';
        }

        if (Str::contains($gameName, 'mobile legends') || preg_match('/\bml\b/i', $gameName) === 1) {
            return 'mobile_legends';
        }

        return null;
    }

    /**
     * @return array<int, array{key: string, label: string, type: string}>
     */
    public function purchaseFields(): array
    {
        if (is_array($this->purchase_fields)) {
            return $this->purchase_fields;
        }

        return match ($this->purchaseDetailsType()) {
            'mobile_legends' => [
                ['key' => 'game_id', 'label' => __('store.game_id'), 'type' => 'text'],
                ['key' => 'game_server', 'label' => __('store.game_server'), 'type' => 'text'],
            ],
            'blox_fruit' => [
                ['key' => 'game_username', 'label' => __('store.game_username'), 'type' => 'text'],
            ],
            'roblox' => [
                ['key' => 'game_username', 'label' => __('store.game_username'), 'type' => 'text'],
                ['key' => 'game_password', 'label' => __('store.game_password'), 'type' => 'password'],
            ],
            default => [],
        };
    }
}

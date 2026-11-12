<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class ProductCurrencyRate extends Model
{
    protected $fillable = [
        'name',
        'idr_per_myr',
        'idr_per_usd',
        'idr_per_robux',
        'idr_per_gamepass_bobux',
        'bobux_gift_ingame_enabled',
        'bobux_gamepass_5_days_enabled',
        'bobux_login_enabled',
    ];

    protected function casts(): array
    {
        return [
            'idr_per_myr' => 'decimal:4',
            'idr_per_usd' => 'decimal:4',
            'idr_per_robux' => 'decimal:4',
            'idr_per_gamepass_bobux' => 'decimal:4',
            'bobux_gift_ingame_enabled' => 'boolean',
            'bobux_gamepass_5_days_enabled' => 'boolean',
            'bobux_login_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['name' => 'default'],
            [
                'idr_per_myr' => config('product_pricing.idr_per_myr'),
                'idr_per_usd' => config('product_pricing.idr_per_usd'),
                'idr_per_robux' => config('product_pricing.idr_per_robux'),
                'idr_per_gamepass_bobux' => config('product_pricing.idr_per_gamepass_bobux'),
                'bobux_gift_ingame_enabled' => true,
                'bobux_gamepass_5_days_enabled' => true,
                'bobux_login_enabled' => true,
            ],
        );
    }

    public function bobuxTransactionsEnabled(?string $bobuxType): bool
    {
        return match ($bobuxType) {
            'gift_ingame' => $this->bobux_gift_ingame_enabled,
            'gamepass_5_days' => $this->bobux_gamepass_5_days_enabled,
            'login' => $this->bobux_login_enabled,
            default => true,
        };
    }

    public function convertIdrTo(float $idrAmount, string $currency): float
    {
        return match ($currency) {
            'idr' => $idrAmount,
            'myr' => self::roundToNearestFiveCents($idrAmount / (float) $this->idr_per_myr),
            'usd' => self::roundToNearestFiveCents($idrAmount / (float) $this->idr_per_usd),
            default => throw new InvalidArgumentException('Unsupported currency: '.$currency),
        };
    }

    public static function roundToNearestFiveCents(float $amount): float
    {
        return round($amount * 20, 0, PHP_ROUND_HALF_UP) / 20;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    /**
     * @var array<string, string>
     */
    private const DEFAULT_TELEGRAM_ACCOUNT_CATEGORIES = [
        'sword' => 'Sword',
        'gun' => 'Gun',
        'fruit' => 'Fruit',
        'gamepass' => 'Gamepass',
        'perm' => 'Perm',
        'awaken' => 'Awaken',
        'race' => 'Race',
    ];

    protected $fillable = [
        'is_online',
        'telegram_account_defaults',
        'telegram_account_rates',
        'telegram_account_categories',
    ];

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'telegram_account_defaults' => 'array',
            'telegram_account_rates' => 'array',
            'telegram_account_categories' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            ['is_online' => true],
        );
    }

    /**
     * @return array{idr_per_myr: string, idr_per_usd: string}
     */
    public function telegramAccountRates(): array
    {
        $storedRates = $this->telegram_account_rates ?? [];
        $websiteRates = ProductCurrencyRate::current();

        return [
            'idr_per_myr' => (string) ($storedRates['idr_per_myr'] ?? $websiteRates->idr_per_myr),
            'idr_per_usd' => (string) ($storedRates['idr_per_usd'] ?? $websiteRates->idr_per_usd),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function telegramAccountCategories(): array
    {
        return array_replace(
            self::DEFAULT_TELEGRAM_ACCOUNT_CATEGORIES,
            $this->telegram_account_categories ?? [],
        );
    }
}

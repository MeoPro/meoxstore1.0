<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'is_online',
        'telegram_account_defaults',
    ];

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'telegram_account_defaults' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            ['is_online' => true],
        );
    }
}

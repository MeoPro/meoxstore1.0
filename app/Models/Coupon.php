<?php

namespace App\Models;

use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'stock',
        'reserved_uses',
        'redeemed_uses',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'stock' => 'integer',
            'reserved_uses' => 'integer',
            'redeemed_uses' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function availableUses(): int
    {
        return max(0, $this->stock - $this->reserved_uses - $this->redeemed_uses);
    }

    public function isAvailable(): bool
    {
        return $this->is_active && $this->availableUses() > 0;
    }

    public function discountAmountFor(float $subtotalInIdr): float
    {
        $discount = match ($this->discount_type) {
            'percent' => $subtotalInIdr * ((float) $this->discount_value / 100),
            'fixed' => (float) $this->discount_value,
            default => throw new LogicException('Unsupported coupon discount type: '.$this->discount_type),
        };

        return min($subtotalInIdr, round($discount, 2));
    }
}

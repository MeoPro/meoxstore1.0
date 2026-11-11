<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'contact_email',
        'contact_phone',
        'status',
        'total',
        'payment_proof_path',
        'payment_details',
        'delivery_details',
        'payment_confirmed_at',
        'completed_at',
        'customer_completed_at',
    ];

    protected $hidden = [
        'delivery_details',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'payment_details' => 'array',
            'delivery_details' => 'encrypted:array',
            'payment_confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'customer_completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if ($order->public_number !== null) {
                return;
            }

            do {
                $publicNumber = 'MEOX-'.Str::upper(Str::random(12));
            } while (self::query()->where('public_number', $publicNumber)->exists());

            $order->public_number = $publicNumber;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function publicNumber(): string
    {
        return (string) $this->public_number;
    }
}

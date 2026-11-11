<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'product_name',
        'product_description',
        'variant_name',
        'quantity',
        'unit_price',
        'robux_amount',
        'customer_details',
    ];

    protected $hidden = [
        'customer_details',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'robux_amount' => 'decimal:2',
            'customer_details' => 'encrypted:array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}

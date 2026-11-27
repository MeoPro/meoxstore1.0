<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('SAVE??##')),
            'discount_type' => 'percent',
            'discount_value' => '10.00',
            'stock' => 10,
            'reserved_uses' => 0,
            'redeemed_uses' => 0,
            'is_active' => true,
        ];
    }
}

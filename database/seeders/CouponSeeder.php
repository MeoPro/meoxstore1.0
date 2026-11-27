<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Coupon::query()->firstOrCreate(
            ['code' => 'EXAMPLE10'],
            [
                'discount_type' => 'percent',
                'discount_value' => '10.00',
                'stock' => 100,
                'is_active' => false,
            ],
        );
    }
}

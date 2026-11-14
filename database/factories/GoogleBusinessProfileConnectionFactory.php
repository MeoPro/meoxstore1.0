<?php

namespace Database\Factories;

use App\Models\GoogleBusinessProfileConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoogleBusinessProfileConnection>
 */
class GoogleBusinessProfileConnectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'refresh_token' => fake()->sha256(),
            'location_name' => 'accounts/123456789/locations/987654321',
            'location_title' => 'Meoxstore',
        ];
    }
}

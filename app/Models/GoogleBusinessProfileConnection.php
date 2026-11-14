<?php

namespace App\Models;

use Database\Factories\GoogleBusinessProfileConnectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleBusinessProfileConnection extends Model
{
    /** @use HasFactory<GoogleBusinessProfileConnectionFactory> */
    use HasFactory;

    protected $fillable = [
        'refresh_token',
        'location_name',
        'location_title',
    ];

    protected $hidden = [
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'refresh_token' => 'encrypted',
        ];
    }
}

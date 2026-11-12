<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = [
        'type',
        'currency',
        'name',
        'bank_name',
        'account_name',
        'account_number',
        'logo_path',
        'qr_code_path',
        'instructions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}

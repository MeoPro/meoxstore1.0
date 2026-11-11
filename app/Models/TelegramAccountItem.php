<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramAccountItem extends Model
{
    protected $fillable = [
        'category',
        'name',
        'emoji',
    ];
}

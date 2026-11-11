<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramAccountDraft extends Model
{
    protected $fillable = [
        'telegram_user_id',
        'chat_id',
        'status',
        'step',
        'data',
        'images',
        'processed_message_ids',
        'last_update_id',
        'channel_media_offset',
        'channel_text_sent',
        'product_id',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'images' => 'array',
            'processed_message_ids' => 'array',
            'channel_text_sent' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

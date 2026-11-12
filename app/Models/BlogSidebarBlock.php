<?php

namespace App\Models;

use Database\Factories\BlogSidebarBlockFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogSidebarBlock extends Model
{
    public const TYPE_PRODUCT = 'product';

    public const TYPE_POSTER = 'poster';

    public const TYPE_BLOG_CATEGORIES = 'blog_categories';

    public const TYPE_GAME_CATEGORIES = 'game_categories';

    public const TYPE_ITEM_CATEGORIES = 'item_categories';

    public const TYPES = [
        self::TYPE_PRODUCT,
        self::TYPE_POSTER,
        self::TYPE_BLOG_CATEGORIES,
        self::TYPE_GAME_CATEGORIES,
        self::TYPE_ITEM_CATEGORIES,
    ];

    /** @use HasFactory<BlogSidebarBlockFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'sort_order',
        'is_active',
        'product_id',
        'image_path',
        'link_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

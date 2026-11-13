<?php

namespace App\Models;

use Database\Factories\BlogPostFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogPost extends Model
{
    /** @use HasFactory<BlogPostFactory> */
    use HasFactory;

    public const LOCALES = ['id', 'ms', 'en'];

    protected $fillable = [
        'translation_group_id',
        'locale',
        'source_locale',
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_image',
        'seo_title',
        'meta_description',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_category_blog_post');
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    #[Scope]
    protected function inLocale(Builder $query, ?string $locale = null): void
    {
        $locale ??= app()->getLocale();

        $query->where(function (Builder $query) use ($locale): void {
            $query->where('locale', $locale)
                ->orWhere(function (Builder $query) use ($locale): void {
                    $query->whereColumn('locale', 'source_locale')
                        ->whereDoesntHave('translations', fn (Builder $translations) => $translations->where('locale', $locale));
                });
        });
    }

    public function translations(): HasMany
    {
        return $this->hasMany(self::class, 'translation_group_id', 'translation_group_id');
    }

    public function isPubliclyAvailable(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }
}

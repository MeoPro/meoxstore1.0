<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\BlogTranslationException;
use App\Services\BlogTranslationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BlogPostController extends Controller
{
    private const TRANSLATABLE_FIELDS = [
        'title',
        'excerpt',
        'body',
        'seo_title',
        'meta_description',
    ];

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $search = is_string($search) ? trim($search) : '';
        $status = $request->query('status');
        $status = is_string($status) && in_array($status, ['draft', 'published'], true) ? $status : '';

        $blogPosts = BlogPost::query()
            ->whereColumn('locale', 'source_locale')
            ->with('categories:id,name')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog-posts.index', compact('blogPosts', 'search', 'status'));
    }

    public function create(): View
    {
        return view('admin.blog-posts.form', [
            'blogPost' => new BlogPost([
                'locale' => BlogPost::LOCALES[0],
                'source_locale' => BlogPost::LOCALES[0],
            ]),
            'categories' => BlogCategory::active()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, BlogTranslationService $translator): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $translations = $this->translatedContent($validated, $translator);
        $translationGroupId = (string) Str::uuid();
        $coverImage = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('blog-covers', 'public')
            : null;

        DB::transaction(function () use ($validated, $translations, $translationGroupId, $coverImage): void {
            $sourceLocale = $validated['source_locale'];
            $sharedAttributes = $this->sharedAttributes($validated);

            foreach ($translations as $locale => $content) {
                $blogPost = new BlogPost([
                    ...$content,
                    ...$sharedAttributes,
                    'translation_group_id' => $translationGroupId,
                    'locale' => $locale,
                    'source_locale' => $sourceLocale,
                    'cover_image' => $coverImage,
                    'slug' => $locale === $sourceLocale
                        ? $this->sourceSlug($validated)
                        : $this->uniqueSlug($content['title'], $locale),
                ]);

                $blogPost->save();
                $blogPost->categories()->sync($validated['categories'] ?? []);
            }
        });

        return redirect()
            ->route('admin.blog-posts.index')
            ->with('success', 'Blog post created and translated into Indonesian, Malay, and English.');
    }

    public function edit(BlogPost $blogPost): View
    {
        $this->ensureSourcePost($blogPost);

        return view('admin.blog-posts.form', [
            'blogPost' => $blogPost->load('categories'),
            'categories' => BlogCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, BlogPost $blogPost, BlogTranslationService $translator): RedirectResponse
    {
        $this->ensureSourcePost($blogPost);
        $validated = $this->validatedData($request, $blogPost);
        $existingTranslations = $blogPost->translations()->get()->keyBy('locale');
        $contentChanged = collect(self::TRANSLATABLE_FIELDS)->contains(
            fn (string $field): bool => ($validated[$field] ?? null) !== $blogPost->{$field},
        );
        $allLocalesExist = $existingTranslations->keys()->sort()->values()->all()
            === collect(BlogPost::LOCALES)->sort()->values()->all();
        $translations = $contentChanged || ! $allLocalesExist
            ? $this->translatedContent($validated, $translator)
            : $existingTranslations->map(fn (BlogPost $translation): array => Arr::only(
                $translation->getAttributes(),
                self::TRANSLATABLE_FIELDS,
            ))->all();

        $previousCoverImage = $blogPost->cover_image;
        $coverImage = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('blog-covers', 'public')
            : $previousCoverImage;
        $translationGroupId = $blogPost->translation_group_id;
        $translationIds = $existingTranslations->modelKeys();
        $sourceSlug = $this->sourceSlug($validated, $blogPost);

        DB::transaction(function () use (
            $blogPost,
            $validated,
            $translations,
            $existingTranslations,
            $translationGroupId,
            $translationIds,
            $sourceSlug,
            $coverImage,
        ): void {
            $sharedAttributes = $this->sharedAttributes($validated);

            foreach ($translations as $locale => $content) {
                $translation = $existingTranslations->get($locale) ?? new BlogPost;
                $translation->fill([
                    ...$content,
                    ...$sharedAttributes,
                    'translation_group_id' => $translationGroupId,
                    'locale' => $locale,
                    'source_locale' => $blogPost->source_locale,
                    'cover_image' => $coverImage,
                    'slug' => $locale === $blogPost->source_locale
                        ? $sourceSlug
                        : $this->uniqueSlug($content['title'], $locale, $translationIds),
                ]);
                $translation->save();
                $translation->categories()->sync($validated['categories'] ?? []);
            }
        });

        if ($previousCoverImage !== null && $coverImage !== $previousCoverImage) {
            Storage::disk('public')->delete($previousCoverImage);
        }

        return redirect()
            ->route('admin.blog-posts.index')
            ->with('success', 'Blog post updated successfully.');
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        $this->ensureSourcePost($blogPost);

        $coverImage = $blogPost->cover_image;
        BlogPost::query()
            ->where('translation_group_id', $blogPost->translation_group_id)
            ->delete();

        if ($coverImage !== null) {
            Storage::disk('public')->delete($coverImage);
        }

        return redirect()
            ->route('admin.blog-posts.index')
            ->with('success', 'Blog post deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?BlogPost $blogPost = null): array
    {
        $request->merge([
            'source_locale' => $blogPost?->source_locale ?? $request->input('source_locale', BlogPost::LOCALES[0]),
        ]);

        $slug = $request->input('slug');

        if (is_string($slug)) {
            $request->merge([
                'slug' => filled($slug) ? Str::slug($slug) : null,
            ]);
        }

        $uniqueSlug = Rule::unique('blog_posts', 'slug');

        if ($blogPost !== null) {
            $uniqueSlug->ignore($blogPost->id);
        }

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', $uniqueSlug],
            'excerpt' => ['required', 'string', 'max:320'],
            'body' => ['required', 'string', 'max:100000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'source_locale' => ['required', 'string', Rule::in($blogPost ? [$blogPost->source_locale] : BlogPost::LOCALES)],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['required', 'integer', 'distinct', Rule::exists('blog_categories', 'id')],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, array<string, string|null>>
     */
    private function translatedContent(array $validated, BlogTranslationService $translator): array
    {
        $sourceLocale = $validated['source_locale'];
        $sourceContent = Arr::only($validated, self::TRANSLATABLE_FIELDS);
        $translations = [$sourceLocale => $sourceContent];

        try {
            foreach (array_diff(BlogPost::LOCALES, [$sourceLocale]) as $targetLocale) {
                $translations[$targetLocale] = [];

                foreach (self::TRANSLATABLE_FIELDS as $field) {
                    $translations[$targetLocale][$field] = $translator->translate(
                        (string) ($sourceContent[$field] ?? ''),
                        $sourceLocale,
                        $targetLocale,
                    );
                }
            }
        } catch (BlogTranslationException $exception) {
            throw ValidationException::withMessages([
                'translation' => $exception->getMessage(),
            ]);
        }

        return $translations;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function sharedAttributes(array $validated): array
    {
        return [
            'status' => $validated['status'],
            'published_at' => $validated['status'] === 'published'
                ? ($validated['published_at'] ?? now())
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function sourceSlug(array $validated, ?BlogPost $blogPost = null): string
    {
        $slug = $validated['slug'] ?? null;

        return blank($slug)
            ? ($blogPost?->slug ?? $this->uniqueSlug($validated['title']))
            : $slug;
    }

    /**
     * @param  array<int, int>  $ignoreIds
     */
    private function uniqueSlug(string $title, ?string $locale = null, array $ignoreIds = []): string
    {
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            if ($locale === null) {
                throw ValidationException::withMessages([
                    'slug' => 'Enter a URL slug because one could not be generated from the title.',
                ]);
            }

            $baseSlug = 'article';
        }

        if ($locale !== null) {
            $baseSlug .= '-'.$locale;
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (BlogPost::query()
            ->where('slug', $slug)
            ->when($ignoreIds !== [], fn (Builder $query) => $query->whereNotIn('id', $ignoreIds))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function ensureSourcePost(BlogPost $blogPost): void
    {
        abort_unless($blogPost->locale === $blogPost->source_locale, 404);
    }
}

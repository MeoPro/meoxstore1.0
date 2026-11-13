<?php

namespace Database\Factories;

use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'translation_group_id' => (string) Str::uuid(),
            'locale' => 'id',
            'source_locale' => 'id',
            'title' => fake()->sentence(5),
            'slug' => fake()->unique()->slug(),
            'excerpt' => fake()->paragraph(),
            'body' => fake()->paragraphs(3, true),
            'cover_image' => null,
            'seo_title' => null,
            'meta_description' => null,
            'status' => 'draft',
            'published_at' => null,
        ];
    }
}

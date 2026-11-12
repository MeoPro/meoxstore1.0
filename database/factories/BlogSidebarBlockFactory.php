<?php

namespace Database\Factories;

use App\Models\BlogSidebarBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogSidebarBlock>
 */
class BlogSidebarBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => BlogSidebarBlock::TYPE_BLOG_CATEGORIES,
            'title' => fake()->sentence(3),
            'sort_order' => 0,
            'is_active' => true,
            'product_id' => null,
            'image_path' => null,
            'link_url' => null,
        ];
    }
}

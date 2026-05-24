<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Enums\TemplateType;
use App\Models\Page;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'parent_id' => null,
            'translation_group_id' => fake()->uuid(),
            'locale' => 'nl',
            'title' => fake()->sentence(3),
            'slug' => fake()->slug(3),
            'full_path' => '/placeholder',
            'template_type' => TemplateType::Default,
            'status' => PageStatus::Published,
            'excerpt_markdown' => 'Introductietekst in **Markdown**.',
            'published_at' => now(),
            'sort_order' => 0,
            'robots_index' => true,
            'robots_follow' => true,
        ];
    }
}

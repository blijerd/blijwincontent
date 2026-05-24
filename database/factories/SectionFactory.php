<?php

namespace Database\Factories;

use App\Enums\SectionType;
use App\Models\Page;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'type' => SectionType::RichText,
            'title' => fake()->sentence(3),
            'intro_markdown' => 'Sectie-intro met **Markdown**.',
            'sort_order' => 0,
            'is_visible' => true,
        ];
    }
}

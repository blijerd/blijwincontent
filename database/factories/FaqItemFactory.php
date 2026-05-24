<?php

namespace Database\Factories;

use App\Models\FaqCategory;
use App\Models\FaqItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FaqItem>
 */
class FaqItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'faq_category_id' => FaqCategory::factory(),
            'question' => fake()->sentence().'?',
            'answer_markdown' => 'Antwoord met **Markdown** en duidelijke uitleg.',
            'is_featured' => false,
            'is_published' => true,
            'sort_order' => 0,
        ];
    }
}

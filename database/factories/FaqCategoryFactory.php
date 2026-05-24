<?php

namespace Database\Factories;

use App\Models\FaqCategory;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FaqCategory>
 */
class FaqCategoryFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->words(2, true);

        return [
            'site_id' => Site::factory(),
            'locale' => 'nl',
            'title' => Str::title($title),
            'slug' => Str::slug($title),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}

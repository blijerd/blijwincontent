<?php

namespace Database\Factories;

use App\Models\DownloadCategory;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<DownloadCategory> */
class DownloadCategoryFactory extends Factory
{
    protected $model = DownloadCategory::class;

    public function definition(): array
    {
        $title = fake()->words(2, true);

        return [
            'site_id' => Site::factory(),
            'locale' => 'nl',
            'title' => $title,
            'slug' => Str::slug($title),
            'intro_markdown' => fake()->optional()->paragraph(),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}

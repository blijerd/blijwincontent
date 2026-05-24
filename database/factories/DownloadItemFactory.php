<?php

namespace Database\Factories;

use App\Models\DownloadCategory;
use App\Models\DownloadItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DownloadItem> */
class DownloadItemFactory extends Factory
{
    protected $model = DownloadItem::class;

    public function definition(): array
    {
        return [
            'download_category_id' => DownloadCategory::factory(),
            'title' => fake()->sentence(3),
            'preview_markdown' => fake()->paragraph(),
            'preview_image_id' => null,
            'preview_image_alt' => null,
            'preview_image_focus' => null,
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}

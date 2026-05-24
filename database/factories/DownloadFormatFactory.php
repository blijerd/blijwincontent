<?php

namespace Database\Factories;

use App\Models\DownloadFormat;
use App\Models\DownloadItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DownloadFormat> */
class DownloadFormatFactory extends Factory
{
    protected $model = DownloadFormat::class;

    public function definition(): array
    {
        return [
            'download_item_id' => DownloadItem::factory(),
            'label' => fake()->randomElement(['PDF', 'DOCX', 'ZIP']),
            'file_path' => 'downloads/'.fake()->slug().'.pdf',
            'is_secure' => false,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    public function secure(): self
    {
        return $this->state(fn (): array => ['is_secure' => true]);
    }
}

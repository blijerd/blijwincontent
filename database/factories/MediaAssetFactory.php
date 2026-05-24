<?php

namespace Database\Factories;

use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaAsset>
 */
class MediaAssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'disk' => 'public',
            'path' => 'media/'.fake()->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => fake()->word().'.jpg',
            'size' => fake()->numberBetween(10000, 2000000),
            'width' => 1600,
            'height' => 900,
            'alt_text' => fake()->sentence(4),
            'locale' => 'nl',
        ];
    }
}

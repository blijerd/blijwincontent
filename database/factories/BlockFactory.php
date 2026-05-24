<?php

namespace Database\Factories;

use App\Enums\BlockType;
use App\Models\Block;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Block>
 */
class BlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'section_id' => Section::factory(),
            'type' => BlockType::Text,
            'sort_order' => 0,
            'heading' => fake()->sentence(3),
            'body_markdown' => fake()->paragraph(),
        ];
    }
}

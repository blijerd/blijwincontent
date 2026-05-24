<?php

namespace Database\Factories;

use App\Models\Redirect;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Redirect>
 */
class RedirectFactory extends Factory
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
            'source_path' => '/oude-pagina',
            'target_url' => '/nieuwe-pagina',
            'status_code' => 301,
            'locale' => 'nl',
        ];
    }
}

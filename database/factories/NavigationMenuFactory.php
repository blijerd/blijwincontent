<?php

namespace Database\Factories;

use App\Models\NavigationMenu;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NavigationMenu>
 */
class NavigationMenuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'handle' => 'main',
            'title' => 'Hoofdmenu',
            'locale' => 'nl',
            'is_active' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\NavigationMenu;
use App\Models\NavigationMenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NavigationMenuItem>
 */
class NavigationMenuItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'navigation_menu_id' => NavigationMenu::factory(),
            'parent_id' => null,
            'page_id' => null,
            'label' => fake()->words(2, true),
            'url' => '/'.fake()->slug(),
            'sort_order' => 0,
            'is_visible' => true,
            'opens_in_new_tab' => false,
        ];
    }
}

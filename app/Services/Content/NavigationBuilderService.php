<?php

namespace App\Services\Content;

use App\Models\NavigationMenu;
use App\Models\NavigationMenuItem;
use App\Models\Site;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NavigationBuilderService
{
    /** @return Collection<int, array<string, mixed>> */
    public function build(Site $site, string $locale, string $handle = 'main'): Collection
    {
        $menu = NavigationMenu::query()
            ->whereBelongsTo($site)
            ->where('locale', $locale)
            ->where('handle', $handle)
            ->where('is_active', true)
            ->with([
                'rootItems' => fn ($query) => $query->where('is_visible', true),
                'rootItems.page',
                'rootItems.children' => fn ($query) => $query->where('is_visible', true),
                'rootItems.children.page',
            ])
            ->first();

        if (! $menu) {
            return collect();
        }

        return $menu->rootItems
            ->map(fn (NavigationMenuItem $item): array => $this->item($item))
            ->values();
    }

    /** @return array<string, mixed> */
    private function item(NavigationMenuItem $item): array
    {
        return [
            'label' => $item->label,
            'url' => $this->url($item),
            'opens_in_new_tab' => $item->opens_in_new_tab,
            'children' => $item->children
                ->where('is_visible', true)
                ->map(fn (NavigationMenuItem $child): array => $this->item($child))
                ->values(),
        ];
    }

    private function url(NavigationMenuItem $item): string
    {
        if ($item->page) {
            return url($item->page->full_path === '/' ? '/' : ltrim($item->page->full_path, '/'));
        }

        if (! $item->url) {
            return '#';
        }

        if (Str::startsWith($item->url, ['http://', 'https://', 'mailto:', 'tel:', '#'])) {
            return $item->url;
        }

        return url($item->url);
    }
}

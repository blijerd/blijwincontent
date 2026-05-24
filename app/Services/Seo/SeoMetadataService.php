<?php

namespace App\Services\Seo;

use App\Enums\PageStatus;
use App\Models\Page;

class SeoMetadataService
{
    /** @return array<string, string|null> */
    public function forPage(Page $page): array
    {
        return [
            'title' => $page->seo_title ?: $page->title,
            'description' => $page->seo_description,
            'canonical' => $page->canonical_url ?: url($page->full_path),
            'og_title' => $page->og_title ?: $page->seo_title ?: $page->title,
            'og_description' => $page->og_description ?: $page->seo_description,
            'robots' => ($page->robots_index ? 'index' : 'noindex').','.($page->robots_follow ? 'follow' : 'nofollow'),
        ];
    }

    /** @return array<string, string> */
    public function hreflang(Page $page): array
    {
        return $page->translations
            ->reject(fn (Page $translation): bool => $translation->is($page))
            ->filter(fn (Page $translation): bool => $translation->is_routable && $this->isPubliclyPublished($translation))
            ->mapWithKeys(fn (Page $translation): array => [$translation->locale => url($translation->full_path)])
            ->prepend(url($page->full_path), $page->locale)
            ->all();
    }

    private function isPubliclyPublished(Page $page): bool
    {
        return $page->status === PageStatus::Published
            && ($page->published_at === null || $page->published_at->lte(now()));
    }
}

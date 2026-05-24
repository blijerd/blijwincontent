<?php

namespace App\Services\Seo;

use App\Enums\SearchIndexingMode;
use App\Models\Page;
use App\Models\Site;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;

class SitemapBuilderService
{
    public function __construct(private readonly Repository $cache)
    {
    }

    public function xmlForSite(Site $site): string
    {
        return $this->cache->remember(
            'sitemap:site:'.$site->id.':indexing:'.$site->search_indexing_mode->value,
            now()->addHour(),
            fn (): string => view('cms.sitemap', [
                'pages' => $site->search_indexing_mode === SearchIndexingMode::Noindex
                    ? collect()
                    : $this->pagesForSite($site),
            ])->render(),
        );
    }

    /**
     * @return Collection<int, Page>
     */
    public function pagesForSite(Site $site): Collection
    {
        return Page::query()
            ->whereBelongsTo($site)
            ->routable()
            ->published()
            ->where('robots_index', true)
            ->orderBy('locale')
            ->orderBy('full_path')
            ->get();
    }
}

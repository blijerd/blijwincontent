<?php

namespace App\Services\Seo;

use App\Enums\SearchIndexingMode;
use App\Models\Site;
use Illuminate\Http\Request;

class SearchIndexingService
{
    public function modeForRequest(Request $request): SearchIndexingMode
    {
        $site = Site::query()
            ->where('domain', $request->getHost())
            ->first();

        if ($site !== null) {
            return $site->search_indexing_mode;
        }

        $fallbackMode = Site::query()
            ->where('is_active', true)
            ->oldest('id')
            ->value('search_indexing_mode');

        return match (true) {
            $fallbackMode instanceof SearchIndexingMode => $fallbackMode,
            $fallbackMode !== null => SearchIndexingMode::from($fallbackMode),
            default => SearchIndexingMode::Index,
        };
    }

    public function blocksIndexing(Site $site): bool
    {
        return $site->search_indexing_mode === SearchIndexingMode::Noindex;
    }
}

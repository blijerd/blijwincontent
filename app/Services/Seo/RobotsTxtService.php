<?php

namespace App\Services\Seo;

use App\Enums\SearchIndexingMode;
use App\Models\Site;

class RobotsTxtService
{
    public function forSite(Site $site, string $rootUrl): string
    {
        if ($site->search_indexing_mode === SearchIndexingMode::Noindex) {
            return "User-agent: *\nDisallow: /\n";
        }

        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Sitemap: '.rtrim($rootUrl, '/').'/sitemap.xml',
            '',
        ]);
    }
}

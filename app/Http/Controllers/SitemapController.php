<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\Seo\SitemapBuilderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(Request $request, SitemapBuilderService $sitemap): Response
    {
        $siteForHost = Site::query()->where('domain', $request->getHost())->first();
        abort_if($siteForHost !== null && ! $siteForHost->is_active, 404);

        $site = $siteForHost ?? Site::query()->where('is_active', true)->firstOrFail();

        return response($sitemap->xmlForSite($site), 200, ['Content-Type' => 'application/xml']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Site;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(Request $request, Repository $cache): Response
    {
        $site = Site::query()->where('domain', $request->getHost())->first()
            ?? Site::query()->where('is_active', true)->firstOrFail();

        $xml = $cache->remember(
            'sitemap:site:'.$site->id,
            now()->addHour(),
            fn (): string => view('cms.sitemap', [
                'pages' => Page::query()
                    ->whereBelongsTo($site)
                    ->published()
                    ->orderBy('locale')
                    ->orderBy('full_path')
                    ->get(),
            ])->render(),
        );

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}

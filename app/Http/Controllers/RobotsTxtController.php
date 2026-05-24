<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\Seo\RobotsTxtService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RobotsTxtController extends Controller
{
    public function __invoke(Request $request, RobotsTxtService $robots): Response
    {
        $siteForHost = Site::query()->where('domain', $request->getHost())->first();
        abort_if($siteForHost !== null && ! $siteForHost->is_active, 404);

        $site = $siteForHost ?? Site::query()->where('is_active', true)->firstOrFail();

        return response($robots->forSite($site, $request->root()), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}

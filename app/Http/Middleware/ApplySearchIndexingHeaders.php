<?php

namespace App\Http\Middleware;

use App\Services\Seo\SearchIndexingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySearchIndexingHeaders
{
    public function __construct(private readonly SearchIndexingService $indexing)
    {
    }

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $robotsHeader = $this->indexing->modeForRequest($request)->robotsHeader();

        if ($robotsHeader !== null) {
            $response->headers->set('X-Robots-Tag', $robotsHeader);
        }

        return $response;
    }
}

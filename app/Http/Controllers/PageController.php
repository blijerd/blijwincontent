<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Site;
use App\Services\Content\PageRenderService;
use App\Services\Seo\SeoMetadataService;
use App\Support\Templates\TemplateConfig;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __invoke(
        Request $request,
        PageRenderService $renderer,
        SeoMetadataService $seo,
        ?string $path = null,
    ): View {
        $site = Site::query()->where('domain', $request->getHost())->first()
            ?? Site::query()->where('is_active', true)->firstOrFail();

        $fullPath = '/'.trim((string) $path, '/');
        $fullPath = $fullPath === '/' ? '/' : rtrim($fullPath, '/');

        $page = Page::query()
            ->with(['sections.blocks.image', 'translations'])
            ->whereBelongsTo($site)
            ->where('full_path', $fullPath)
            ->published()
            ->firstOrFail();

        return view(TemplateConfig::view($page->template_type), [
            'viewModel' => $renderer->viewModel($page),
            'seo' => $seo->forPage($page),
            'hreflang' => $seo->hreflang($page),
        ]);
    }
}

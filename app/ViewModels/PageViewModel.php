<?php

namespace App\ViewModels;

use App\Models\Page;
use App\Services\Content\MarkdownRenderService;
use App\Services\Content\NavigationBuilderService;
use App\Services\Downloads\DownloadCatalogBuilderService;
use App\Services\Faq\FaqBuilderService;
use Illuminate\Support\Collection;

class PageViewModel
{
    public function __construct(
        public readonly Page $page,
        private readonly MarkdownRenderService $markdown,
        private readonly FaqBuilderService $faqBuilder,
        private readonly DownloadCatalogBuilderService $downloadCatalogBuilder,
        private readonly NavigationBuilderService $navigationBuilder,
    ) {}

    public function excerptHtml(): string
    {
        return $this->markdown->render($this->page->excerpt_markdown, "page:{$this->page->public_id}:excerpt");
    }

    /** @return Collection<int, array<string, mixed>> */
    public function sections(): Collection
    {
        return $this->page->sections
            ->where('is_visible', true)
            ->values()
            ->map(fn ($section): array => [
                'model' => $section,
                'intro_html' => $this->markdown->render($section->intro_markdown, "section:{$section->public_id}:intro"),
                'faq' => $this->faqBuilder->build($section),
                'downloads' => $this->downloadCatalogBuilder->build($section),
                'blocks' => $section->blocks->map(fn ($block): array => [
                    'model' => $block,
                    'body_html' => $this->markdown->render($block->body_markdown, "block:{$block->public_id}:body"),
                ]),
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    public function mainNavigation(): Collection
    {
        return $this->navigationBuilder->build($this->page->site, $this->page->locale, 'main');
    }

    /** @return Collection<int, array<string, mixed>> */
    public function audienceNavigation(): Collection
    {
        return $this->navigationBuilder->build($this->page->site, $this->page->locale, 'audience');
    }
}

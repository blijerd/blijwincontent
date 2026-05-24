<?php

namespace App\Services\Content;

use App\Models\Page;
use App\Services\Downloads\DownloadCatalogBuilderService;
use App\Services\Faq\FaqBuilderService;
use App\ViewModels\PageViewModel;

class PageRenderService
{
    public function __construct(
        private readonly MarkdownRenderService $markdown,
        private readonly FaqBuilderService $faqBuilder,
        private readonly DownloadCatalogBuilderService $downloadCatalogBuilder,
    ) {}

    public function viewModel(Page $page): PageViewModel
    {
        $page->loadMissing([
            'site',
            'sections.blocks.image',
            'sections.faqCategories.items',
            'sections.downloadCategories.items.formats',
            'sections.downloadCategories.items.previewImage',
            'translations',
        ]);

        return new PageViewModel($page, $this->markdown, $this->faqBuilder, $this->downloadCatalogBuilder);
    }
}

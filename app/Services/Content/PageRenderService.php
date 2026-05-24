<?php

namespace App\Services\Content;

use App\Models\Page;
use App\Services\Faq\FaqBuilderService;
use App\ViewModels\PageViewModel;

class PageRenderService
{
    public function __construct(
        private readonly MarkdownRenderService $markdown,
        private readonly FaqBuilderService $faqBuilder,
    ) {}

    public function viewModel(Page $page): PageViewModel
    {
        $page->loadMissing(['site', 'sections.blocks.image', 'sections.faqCategories.items', 'translations']);

        return new PageViewModel($page, $this->markdown, $this->faqBuilder);
    }
}

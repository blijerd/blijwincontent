<?php

namespace App\Services\Content;

use App\Models\Page;
use App\ViewModels\PageViewModel;

class PageRenderService
{
    public function __construct(
        private readonly MarkdownRenderService $markdown,
    ) {
    }

    public function viewModel(Page $page): PageViewModel
    {
        $page->loadMissing(['site', 'sections.blocks.image', 'translations']);

        return new PageViewModel($page, $this->markdown);
    }
}

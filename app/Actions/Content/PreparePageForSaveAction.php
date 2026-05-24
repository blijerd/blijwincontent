<?php

namespace App\Actions\Content;

use App\Models\Page;
use Illuminate\Support\Str;

class PreparePageForSaveAction
{
    public function __construct(private readonly GeneratePagePathAction $generatePagePath)
    {
    }

    public function execute(Page $page): Page
    {
        $page->slug = Str::slug($page->slug ?: $page->title);

        if ($page->translation_group_id === null) {
            $page->translation_group_id = (string) Str::uuid();
        }

        $page->loadMissing('parent');
        $page->full_path = $this->generatePagePath->execute($page);

        return $page;
    }
}

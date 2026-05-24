<?php

namespace App\Actions\Content;

use App\Models\Page;
use Illuminate\Support\Str;

class GeneratePagePathAction
{
    public function execute(Page $page): string
    {
        $slug = Str::slug($page->slug ?: $page->title);

        if ($page->parent === null) {
            return $slug === 'home' ? '/' : "/{$slug}";
        }

        $parentPath = rtrim($page->parent->full_path, '/');

        return "{$parentPath}/{$slug}";
    }
}

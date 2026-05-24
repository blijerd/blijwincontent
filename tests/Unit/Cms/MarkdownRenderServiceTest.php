<?php

namespace Tests\Unit\Cms;

use App\Services\Content\MarkdownRenderService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MarkdownRenderServiceTest extends TestCase
{
    public function test_it_renders_markdown_and_strips_unsafe_html(): void
    {
        $html = app(MarkdownRenderService::class)->render('# Titel<script>alert(1)</script>', 'test');

        $this->assertStringContainsString('<h1', $html);
        $this->assertStringContainsString('Titel', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_it_caches_rendered_markdown(): void
    {
        Cache::flush();

        app(MarkdownRenderService::class)->render('**cached**', 'cache-test');

        $this->assertTrue(Cache::has('markdown:'.sha1('cache-test|**cached**')));
    }
}

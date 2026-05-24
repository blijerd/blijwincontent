<?php

namespace Tests\Feature\Cms;

use App\Enums\PageStatus;
use App\Enums\SectionType;
use App\Enums\TemplateType;
use App\Models\Block;
use App\Models\Page;
use App\Models\Section;
use App\Models\Site;
use App\Services\Grav\GravContentImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GravContentImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_grav_pages_modules_ordering_and_frontmatter(): void
    {
        $root = storage_path('framework/testing/grav-pages');
        File::deleteDirectory($root);
        File::ensureDirectoryExists("{$root}/01.home/01._hero");
        File::ensureDirectoryExists("{$root}/01.home/02._triplets");
        File::ensureDirectoryExists("{$root}/04.blogs/01.eerste-blog");

        File::put("{$root}/01.home/modular.md", <<<'MD'
---
title: Blijwin
template: modular
published: true
visible: true
seo-magic:
    title: SEO titel
    description: SEO omschrijving
content:
    items: '@self.modular'
---

Welkom bij Blijwin
MD);

        File::put("{$root}/01.home/01._hero/hero.md", <<<'MD'
---
title: Hero
custom:
    heroType: hp-bg
---

# Ik ben Blijwin
## Samen blij
MD);

        File::put("{$root}/01.home/02._triplets/triplets.md", <<<'MD'
---
titel: Kaarten
kaarten:
    -
        titel: Kinderfeestje
        tekst: Feesttekst
        ctaTekst: Boeken
        ctaUrl: /boeken
---
MD);

        File::put("{$root}/04.blogs/blog_overview.md", <<<'MD'
---
title: Blog
published: true
content:
    items: '@self.children'
---

Welkom op mijn blog.
MD);

        File::put("{$root}/04.blogs/01.eerste-blog/blog_record.md", <<<'MD'
---
title: Eerste blog
publish_date: '07-04-2023 7:26'
taxonomy:
    tag:
        - test
---

Blog body.
MD);

        $site = Site::factory()->create(['domain' => 'localhost']);

        $stats = app(GravContentImportService::class)->import($root, $site);

        $this->assertSame(3, $stats['pages']);
        $this->assertSame(5, Section::count());
        $this->assertSame(5, Block::count());

        $home = Page::where('source_path', '01.home/modular.md')->firstOrFail();
        $this->assertSame('/', $home->full_path);
        $this->assertSame(1, $home->sort_order);
        $this->assertSame(TemplateType::LandingPage, $home->template_type);
        $this->assertSame(PageStatus::Published, $home->status);
        $this->assertSame('SEO titel', $home->seo_title);
        $this->assertSame('@self.modular', $home->source_frontmatter['content']['items']);

        $hero = Section::where('source_path', '01.home/01._hero/hero.md')->firstOrFail();
        $this->assertSame($home->id, $hero->page_id);
        $this->assertSame(SectionType::Hero, $hero->type);
        $this->assertSame(1, $hero->sort_order);
        $this->assertSame('hp-bg', $hero->source_frontmatter['custom']['heroType']);

        $tripletBlock = Block::where('source_key', 'item:0')->firstOrFail();
        $this->assertSame('Kinderfeestje', $tripletBlock->heading);
        $this->assertSame('Feesttekst', $tripletBlock->body_markdown);
        $this->assertSame('/boeken', $tripletBlock->button_url);
        $this->assertSame('Kinderfeestje', $tripletBlock->source_payload['titel']);

        $blog = Page::where('source_path', '04.blogs/01.eerste-blog/blog_record.md')->firstOrFail();
        $this->assertSame('/blogs/eerste-blog', $blog->full_path);
        $this->assertSame(TemplateType::Blog, $blog->template_type);
        $this->assertSame('test', $blog->source_frontmatter['taxonomy']['tag'][0]);
        $this->assertSame('2023-04-07', $blog->published_at?->toDateString());
    }
}

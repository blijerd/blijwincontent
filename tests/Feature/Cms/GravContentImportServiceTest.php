<?php

namespace Tests\Feature\Cms;

use App\Enums\PageStatus;
use App\Enums\SectionType;
use App\Enums\TemplateType;
use App\Actions\Grav\ImportDeploymentGravPagesAction;
use App\Models\Block;
use App\Models\NavigationMenu;
use App\Models\NavigationMenuItem;
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
        $this->assertSame(2, $stats['menus']);
        $this->assertGreaterThanOrEqual(2, $stats['menu_items']);
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

        $audienceMenu = NavigationMenu::where('handle', 'audience')->firstOrFail();
        $this->assertDatabaseHas('navigation_menu_items', [
            'navigation_menu_id' => $audienceMenu->id,
            'label' => 'Voor boekers',
            'url' => '/',
        ]);
        $this->assertDatabaseHas('navigation_menu_items', [
            'navigation_menu_id' => $audienceMenu->id,
            'label' => 'Voor fans',
            'url' => '/fans',
        ]);
    }

    public function test_it_imports_legacy_header_menu_with_submenu_items(): void
    {
        $root = storage_path('framework/testing/grav-navigation');
        File::deleteDirectory($root);
        File::ensureDirectoryExists("{$root}/03.kinderdisco/04.camping-kinderdisco");

        File::put("{$root}/03.kinderdisco/modular.md", <<<'MD'
---
title: Kinderdisco
menu: Kinderdisco
template: modular
published: true
visible: true
---
MD);

        File::put("{$root}/03.kinderdisco/04.camping-kinderdisco/modular.md", <<<'MD'
---
title: Camping Kinderdisco
menu: Camping
template: modular
published: true
visible: true
---
MD);

        $site = Site::factory()->create(['domain' => 'localhost']);

        app(GravContentImportService::class)->import($root, $site);

        $mainMenu = NavigationMenu::where('handle', 'main')->firstOrFail();
        $mainItem = NavigationMenuItem::where('navigation_menu_id', $mainMenu->id)
            ->where('label', 'Kinderdisco')
            ->firstOrFail();

        $this->assertDatabaseHas('navigation_menu_items', [
            'navigation_menu_id' => $mainMenu->id,
            'parent_id' => $mainItem->id,
            'label' => 'Camping',
        ]);
    }

    public function test_deployment_import_imports_bundled_pages_and_creates_site_when_needed(): void
    {
        $root = storage_path('framework/testing/deployment-grav-pages');
        File::deleteDirectory($root);
        File::ensureDirectoryExists("{$root}/01.home");
        File::put("{$root}/01.home/modular.md", <<<'MD'
---
title: Blijwin
template: modular
published: true
---

Welkom.
MD);

        config()->set('settings.grav_page_import', [
            'enabled' => true,
            'path' => $root,
            'locale' => 'nl',
            'site' => null,
            'site_domain' => 'cms.example.test',
        ]);

        $this->artisan('cms:import-deployment-grav-pages')->assertSuccessful();

        $this->assertDatabaseHas('sites', [
            'domain' => 'cms.example.test',
            'default_locale' => 'nl',
        ]);

        $this->assertDatabaseHas('pages', [
            'source_system' => 'grav',
            'source_path' => '01.home/modular.md',
            'full_path' => '/',
        ]);
    }

    public function test_deployment_import_skips_existing_grav_pages_unless_forced(): void
    {
        $root = storage_path('framework/testing/deployment-grav-pages-skip');
        File::deleteDirectory($root);
        File::ensureDirectoryExists("{$root}/01.home");
        File::put("{$root}/01.home/modular.md", <<<'MD'
---
title: Blijwin
template: modular
published: true
---

Welkom.
MD);

        config()->set('settings.grav_page_import', [
            'enabled' => true,
            'path' => $root,
            'locale' => 'nl',
            'site' => null,
            'site_domain' => 'cms.example.test',
        ]);

        $this->artisan('cms:import-deployment-grav-pages')->assertSuccessful();

        $this->artisan('cms:import-deployment-grav-pages')
            ->expectsOutput('Skipped deployment Grav page import: already_imported.')
            ->assertSuccessful();

        $this->assertSame(1, Page::query()->where('source_system', 'grav')->count());
    }

    public function test_bundled_deployment_snapshot_is_importable(): void
    {
        config()->set('settings.grav_page_import', [
            'enabled' => true,
            'path' => database_path('imports/grav-pages'),
            'locale' => 'nl',
            'site' => null,
            'site_domain' => 'cms.example.test',
        ]);

        $stats = app(ImportDeploymentGravPagesAction::class)->execute(force: true);

        $this->assertFalse($stats['skipped']);
        $this->assertGreaterThan(100, $stats['pages']);
        $this->assertGreaterThan(50, $stats['sections']);
        $this->assertGreaterThan(50, $stats['blocks']);

        $this->assertDatabaseHas('pages', [
            'source_path' => '01.home/modular.md',
            'full_path' => '/',
        ]);
        $this->assertDatabaseMissing('pages', [
            'source_path' => 'root.md',
        ]);
    }
}

<?php

namespace Tests\Feature\Cms;

use App\Enums\PageStatus;
use App\Enums\SectionType;
use App\Enums\TemplateType;
use App\Models\Block;
use App\Models\Page;
use App\Models\Section;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_a_published_landing_page(): void
    {
        $site = Site::factory()->create(['domain' => 'localhost']);
        $page = Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Home',
            'slug' => 'home',
            'template_type' => TemplateType::LandingPage,
            'status' => PageStatus::Published,
        ]);
        $section = Section::factory()->create([
            'page_id' => $page->id,
            'type' => SectionType::Hero,
            'title' => 'Welkom',
            'intro_markdown' => 'Intro met **markdown**.',
        ]);
        Block::factory()->create(['section_id' => $section->id]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Welkom')
            ->assertSee('Intro met', false);
    }

    public function test_it_does_not_render_pages_for_inactive_sites(): void
    {
        Site::factory()->create(['domain' => 'active.test']);
        $site = Site::factory()->create([
            'domain' => 'localhost',
            'is_active' => false,
        ]);
        Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Inactieve pagina',
            'slug' => 'inactieve-pagina',
            'status' => PageStatus::Published,
        ]);

        $this->get('/inactieve-pagina')->assertNotFound();
    }

    public function test_it_does_not_render_non_routable_pages(): void
    {
        $site = Site::factory()->create(['domain' => 'localhost']);
        Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Verborgen module',
            'slug' => 'verborgen-module',
            'status' => PageStatus::Published,
            'is_routable' => false,
        ]);

        $this->get('/verborgen-module')->assertNotFound();
    }

    public function test_hreflang_only_contains_publicly_available_translations(): void
    {
        $site = Site::factory()->create(['domain' => 'localhost']);
        $translationGroup = fake()->uuid();

        Page::factory()->create([
            'site_id' => $site->id,
            'translation_group_id' => $translationGroup,
            'locale' => 'nl',
            'title' => 'Nederlandse pagina',
            'slug' => 'nederlandse-pagina',
            'status' => PageStatus::Published,
        ]);
        Page::factory()->create([
            'site_id' => $site->id,
            'translation_group_id' => $translationGroup,
            'locale' => 'en',
            'title' => 'English page',
            'slug' => 'english-page',
            'status' => PageStatus::Published,
        ]);
        Page::factory()->create([
            'site_id' => $site->id,
            'translation_group_id' => $translationGroup,
            'locale' => 'de',
            'title' => 'Future page',
            'slug' => 'future-page',
            'status' => PageStatus::Published,
            'published_at' => now()->addDay(),
        ]);
        Page::factory()->create([
            'site_id' => $site->id,
            'translation_group_id' => $translationGroup,
            'locale' => 'fr',
            'title' => 'Internal page',
            'slug' => 'internal-page',
            'status' => PageStatus::Published,
            'is_routable' => false,
        ]);

        $this->get('/nederlandse-pagina')
            ->assertOk()
            ->assertSee('hreflang="nl"', false)
            ->assertSee('http://localhost/nederlandse-pagina', false)
            ->assertSee('hreflang="en"', false)
            ->assertSee('http://localhost/english-page', false)
            ->assertDontSee('future-page')
            ->assertDontSee('internal-page');
    }
}

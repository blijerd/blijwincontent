<?php

namespace Tests\Feature\Cms;

use App\Enums\PageStatus;
use App\Enums\SectionType;
use App\Enums\TemplateType;
use App\Models\Block;
use App\Models\NavigationMenu;
use App\Models\NavigationMenuItem;
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

    public function test_it_renders_admin_configured_header_navigation(): void
    {
        $site = Site::factory()->create(['domain' => 'localhost']);
        $home = Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Home',
            'slug' => 'home',
            'status' => PageStatus::Published,
        ]);
        $parent = Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Kinderdisco',
            'slug' => 'kinderdisco',
            'status' => PageStatus::Published,
        ]);
        $child = Page::factory()->create([
            'site_id' => $site->id,
            'parent_id' => $parent->id,
            'title' => 'Camping',
            'slug' => 'camping',
            'status' => PageStatus::Published,
        ]);
        $mainMenu = NavigationMenu::factory()->create([
            'site_id' => $site->id,
            'handle' => 'main',
        ]);
        $audienceMenu = NavigationMenu::factory()->create([
            'site_id' => $site->id,
            'handle' => 'audience',
            'title' => 'Publiekskeuze',
        ]);
        $mainItem = NavigationMenuItem::factory()->create([
            'navigation_menu_id' => $mainMenu->id,
            'page_id' => $parent->id,
            'label' => 'Kinderdisco',
            'url' => null,
        ]);
        NavigationMenuItem::factory()->create([
            'navigation_menu_id' => $mainMenu->id,
            'parent_id' => $mainItem->id,
            'page_id' => $child->id,
            'label' => 'Camping',
            'url' => null,
        ]);
        NavigationMenuItem::factory()->create([
            'navigation_menu_id' => $audienceMenu->id,
            'label' => 'Voor boekers',
            'url' => '/',
        ]);
        NavigationMenuItem::factory()->create([
            'navigation_menu_id' => $audienceMenu->id,
            'label' => 'Voor fans',
            'url' => '/fans',
        ]);

        $this->get($home->full_path)
            ->assertOk()
            ->assertSee('Voor boekers')
            ->assertSee('Voor fans')
            ->assertSee('Kinderdisco')
            ->assertSee('Camping');
    }

    public function test_it_renders_imported_storyflow_blocks_with_privacy_friendly_youtube(): void
    {
        $site = Site::factory()->create(['domain' => 'localhost']);
        $page = Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Kinderfeestje',
            'slug' => 'kinderfeestje',
            'template_type' => TemplateType::LandingPage,
            'status' => PageStatus::Published,
        ]);
        $section = Section::factory()->create([
            'page_id' => $page->id,
            'type' => SectionType::RichText,
            'title' => 'Storyflow',
            'source_template' => 'storyflow',
        ]);
        Block::factory()->create([
            'section_id' => $section->id,
            'heading' => 'Kinder-DJ Blijwin boeken',
            'subheading' => 'Verhaal',
            'body_markdown' => 'Wil je mij boeken? Omdat je me kent van Social Media.',
            'source_payload' => [
                'mediaType' => 'youtube',
                'youtubeCode' => 'xzL6sx1VhHs',
                'layout' => 'media-rechts',
            ],
        ]);

        $this->get('/kinderfeestje')
            ->assertOk()
            ->assertSee('Kinder-DJ Blijwin boeken')
            ->assertSee('Wil je mij boeken?', false)
            ->assertSee('data-youtube-src="https://www.youtube-nocookie.com/embed/xzL6sx1VhHs?autoplay=1&amp;rel=0&amp;modestbranding=1"', false)
            ->assertDontSee('<iframe', false);
    }

    public function test_it_renders_imported_two_column_media_blocks(): void
    {
        $site = Site::factory()->create(['domain' => 'localhost']);
        $page = Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Contact',
            'slug' => 'contact',
            'template_type' => TemplateType::LandingPage,
            'status' => PageStatus::Published,
        ]);
        $section = Section::factory()->create([
            'page_id' => $page->id,
            'type' => SectionType::TwoColumns,
            'title' => 'Video',
            'source_template' => '2koloms',
        ]);
        Block::factory()->create([
            'section_id' => $section->id,
            'body_markdown' => 'xzL6sx1VhHs',
            'source_payload' => [
                'type' => 'video',
                'content' => 'xzL6sx1VhHs',
            ],
        ]);

        $this->get('/contact')
            ->assertOk()
            ->assertSee('youtube-nocookie.com/embed/xzL6sx1VhHs', false)
            ->assertDontSee('<iframe', false);
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

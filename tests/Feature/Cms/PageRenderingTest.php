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
}

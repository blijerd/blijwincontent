<?php

namespace Tests\Feature\Cms;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_outputs_published_pages_in_the_sitemap(): void
    {
        $site = Site::factory()->create(['domain' => 'localhost']);
        Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Sitemap pagina',
            'slug' => 'sitemap-pagina',
            'status' => PageStatus::Published,
        ]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('content-type', 'application/xml')
            ->assertSee('<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>', false)
            ->assertSee('<urlset', false)
            ->assertSee('/sitemap-pagina');
    }

    public function test_it_excludes_pages_that_should_not_be_publicly_indexed(): void
    {
        $site = Site::factory()->create(['domain' => 'localhost']);
        Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Publieke pagina',
            'slug' => 'publieke-pagina',
            'status' => PageStatus::Published,
        ]);
        Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Interne module',
            'slug' => 'interne-module',
            'status' => PageStatus::Published,
            'is_routable' => false,
        ]);
        Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Noindex pagina',
            'slug' => 'noindex-pagina',
            'status' => PageStatus::Published,
            'robots_index' => false,
        ]);
        Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Concept pagina',
            'slug' => 'concept-pagina',
            'status' => PageStatus::Draft,
        ]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/publieke-pagina')
            ->assertDontSee('/interne-module')
            ->assertDontSee('/noindex-pagina')
            ->assertDontSee('/concept-pagina');
    }

    public function test_it_does_not_output_a_sitemap_for_inactive_sites(): void
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

        $this->get('/sitemap.xml')->assertNotFound();
    }
}

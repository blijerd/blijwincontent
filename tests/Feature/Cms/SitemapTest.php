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
            ->assertSee('<urlset', false)
            ->assertSee('/sitemap-pagina');
    }
}

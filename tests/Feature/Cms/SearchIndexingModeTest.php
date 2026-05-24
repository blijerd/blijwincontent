<?php

namespace Tests\Feature\Cms;

use App\Enums\PageStatus;
use App\Enums\SearchIndexingMode;
use App\Models\Page;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchIndexingModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_allows_crawling_and_points_to_the_sitemap_when_indexing_is_enabled(): void
    {
        Site::factory()->create([
            'domain' => 'localhost',
            'search_indexing_mode' => SearchIndexingMode::Index,
        ]);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8')
            ->assertSee("User-agent: *\nAllow: /\nSitemap: http://localhost/sitemap.xml", false)
            ->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_robots_txt_and_headers_block_indexing_when_noindex_is_enabled(): void
    {
        $site = Site::factory()->create([
            'domain' => 'localhost',
            'search_indexing_mode' => SearchIndexingMode::Noindex,
        ]);
        Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Geblokkeerde pagina',
            'slug' => 'geblokkeerde-pagina',
            'status' => PageStatus::Published,
        ]);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee("User-agent: *\nDisallow: /", false)
            ->assertDontSee('Sitemap:');

        $this->get('/geblokkeerde-pagina')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('<meta name="robots" content="noindex,nofollow">', false);
    }

    public function test_sitemap_is_empty_when_site_indexing_is_blocked(): void
    {
        $site = Site::factory()->create([
            'domain' => 'localhost',
            'search_indexing_mode' => SearchIndexingMode::Noindex,
        ]);
        Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Niet in sitemap',
            'slug' => 'niet-in-sitemap',
            'status' => PageStatus::Published,
        ]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee('/niet-in-sitemap');
    }
}

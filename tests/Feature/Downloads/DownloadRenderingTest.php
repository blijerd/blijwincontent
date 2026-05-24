<?php

namespace Tests\Feature\Downloads;

use App\Enums\PageStatus;
use App\Enums\SectionType;
use App\Enums\TemplateType;
use App\Models\DownloadCategory;
use App\Models\DownloadFormat;
use App\Models\DownloadItem;
use App\Models\Page;
use App\Models\Section;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_download_categories_items_formats_and_secure_controls(): void
    {
        $site = Site::factory()->create(['domain' => 'localhost']);
        $page = Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Downloads',
            'slug' => 'downloads',
            'full_path' => '/downloads',
            'template_type' => TemplateType::Downloads,
            'status' => PageStatus::Published,
        ]);
        $section = Section::factory()->create([
            'page_id' => $page->id,
            'type' => SectionType::Downloads,
            'title' => 'Handige downloads',
        ]);
        $category = DownloadCategory::factory()->create([
            'site_id' => $site->id,
            'title' => 'Brochures',
            'slug' => 'brochures',
            'intro_markdown' => 'Alles voor je **feest**.',
        ]);
        $item = DownloadItem::factory()->create([
            'download_category_id' => $category->id,
            'title' => 'Schuimparty brochure',
            'preview_markdown' => 'Download de complete brochure.',
        ]);
        DownloadFormat::factory()->create([
            'download_item_id' => $item->id,
            'label' => 'PDF',
            'file_path' => 'downloads/schuimparty.pdf',
            'sort_order' => 1,
        ]);
        DownloadFormat::factory()->secure()->create([
            'download_item_id' => $item->id,
            'label' => 'Prijslijst',
            'file_path' => 'downloads/prijslijst.pdf',
            'sort_order' => 2,
        ]);
        $section->downloadCategories()->attach($category);

        $this->get('/downloads')
            ->assertOk()
            ->assertSee('Handige downloads')
            ->assertSee('Brochures')
            ->assertSee('Alles voor je', false)
            ->assertSee('Schuimparty brochure')
            ->assertSee(route('downloads.file', [
                'category' => $category->public_id,
                'item' => $item->public_id,
                'format' => $item->formats()->where('label', 'PDF')->firstOrFail()->public_id,
            ]), false)
            ->assertSee('data-download-secure', false)
            ->assertSee('/downloads/api/request-email', false);
    }
}

<?php

namespace Tests\Feature\Faq;

use App\Enums\PageStatus;
use App\Enums\SectionType;
use App\Enums\TemplateType;
use App\Models\FaqCategory;
use App\Models\FaqItem;
use App\Models\Page;
use App\Models\Section;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_central_faq_items_with_tokens_and_schema(): void
    {
        $site = Site::factory()->create(['domain' => 'localhost']);
        $page = Page::factory()->create([
            'site_id' => $site->id,
            'title' => 'Veelgestelde vragen',
            'slug' => 'faq',
            'full_path' => '/faq',
            'template_type' => TemplateType::Default,
            'status' => PageStatus::Published,
        ]);
        $section = Section::factory()->create([
            'page_id' => $page->id,
            'type' => SectionType::Faq,
            'title' => 'Veelgestelde vragen',
            'faq_keyword' => 'schoolfeest',
            'faq_schema_enabled' => true,
        ]);
        $category = FaqCategory::factory()->create([
            'site_id' => $site->id,
            'title' => 'Praktisch',
            'slug' => 'praktisch',
        ]);
        $section->faqCategories()->attach($category);
        FaqItem::factory()->create([
            'faq_category_id' => $category->id,
            'question' => 'Wat kost een {trefwoord}?',
            'answer_markdown' => 'De prijs hangt af van het gekozen **pakket**.',
            'sort_order' => 1,
        ]);

        $this->get('/faq')
            ->assertOk()
            ->assertSee('Wat kost een schoolfeest?')
            ->assertSee('De prijs hangt af van het gekozen', false)
            ->assertSee('FAQPage')
            ->assertSee('data-faq-search', false);
    }
}

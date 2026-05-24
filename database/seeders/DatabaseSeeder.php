<?php

namespace Database\Seeders;

use App\Enums\BlockType;
use App\Enums\PageStatus;
use App\Enums\SectionType;
use App\Enums\TemplateType;
use App\Models\Block;
use App\Models\Page;
use App\Models\Section;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'CMS Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $site = Site::factory()->create([
            'name' => 'Blijwin Content CMS',
            'domain' => 'localhost',
            'available_locales' => ['nl', 'en'],
        ]);

        foreach ([
            ['Welkom', 'home', TemplateType::LandingPage],
            ['Blog voorbeeld', 'blog', TemplateType::Blog],
            ['Product voorbeeld', 'product', TemplateType::Product],
        ] as [$title, $slug, $template]) {
            $page = Page::factory()->create([
                'site_id' => $site->id,
                'title' => $title,
                'slug' => $slug,
                'template_type' => $template,
                'status' => PageStatus::Published,
            ]);

            $section = Section::factory()->create([
                'page_id' => $page->id,
                'type' => SectionType::Hero,
                'title' => $title,
                'intro_markdown' => 'Een snelle markdown-first pagina die later logisch vanuit GRAV gemapt kan worden.',
            ]);

            Block::factory()->create([
                'section_id' => $section->id,
                'type' => BlockType::Text,
                'heading' => 'Markdown content',
                'body_markdown' => "## Structuur\n\nContent staat relationeel in pagina's, secties en blokken.",
            ]);

            $contentSection = Section::factory()->create([
                'page_id' => $page->id,
                'type' => SectionType::Triplets,
                'title' => 'Gebouwd voor vrolijke content',
                'intro_markdown' => 'De website gebruikt dezelfde herkenbare Blijwin OS vormentaal, maar voelt als een publieke website.',
                'sort_order' => 10,
            ]);

            foreach (['Markdown-first', 'Meertalig voorbereid', 'SEO als basis'] as $index => $heading) {
                Block::factory()->create([
                    'section_id' => $contentSection->id,
                    'type' => BlockType::Text,
                    'sort_order' => $index,
                    'heading' => $heading,
                    'body_markdown' => 'Zachte cards, duidelijke tekst en een relationele structuur die later netjes met GRAV-content kan samenwerken.',
                ]);
            }

            $ctaSection = Section::factory()->create([
                'page_id' => $page->id,
                'type' => SectionType::Cta,
                'title' => 'Klaar voor de volgende stap',
                'intro_markdown' => 'Beheer pagina’s, secties, blokken, media en redirects vanuit Filament.',
                'sort_order' => 20,
            ]);

            Block::factory()->create([
                'section_id' => $ctaSection->id,
                'type' => BlockType::Button,
                'button_label' => 'Naar de admin',
                'button_url' => '/admin',
            ]);
        }
    }
}

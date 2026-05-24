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
        }
    }
}

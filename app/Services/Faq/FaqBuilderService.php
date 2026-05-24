<?php

namespace App\Services\Faq;

use App\Enums\BlockType;
use App\Models\FaqItem;
use App\Models\Section;
use App\Services\Content\MarkdownRenderService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FaqBuilderService
{
    public function __construct(private readonly MarkdownRenderService $markdown) {}

    /** @return array<string, mixed> */
    public function build(Section $section): array
    {
        $keyword = trim((string) ($section->faq_keyword ?: 'disco'));
        $tokens = [
            '{trefwoord}' => $keyword,
            '{keyword}' => $keyword,
        ];

        $categories = $section->faqCategories
            ->where('is_active', true)
            ->sortBy([['sort_order', 'asc'], ['title', 'asc']])
            ->values();

        $items = $categories->isNotEmpty()
            ? $this->itemsFromCategories($section, $tokens)
            : $this->itemsFromBlocks($section, $tokens);

        return [
            'title' => $section->title,
            'searchable' => (bool) $section->faq_searchable,
            'categories_enabled' => (bool) $section->faq_categories_enabled && $categories->isNotEmpty(),
            'schema_enabled' => (bool) $section->faq_schema_enabled,
            'expand_first' => (bool) $section->faq_expand_first,
            'allow_multiple_open' => (bool) $section->faq_allow_multiple_open,
            'initial_limit' => (int) $section->faq_initial_limit,
            'cta_label' => $section->faq_cta_label,
            'cta_url' => $section->faq_cta_url,
            'categories' => $categories->map(fn ($category): array => [
                'id' => $category->public_id,
                'title' => $this->replaceTokens($category->title, $tokens),
                'slug' => $category->slug,
            ])->all(),
            'items' => $items,
            'schema' => $this->schemaFor($section, $items),
        ];
    }

    /** @param array<string, string> $tokens */
    private function itemsFromCategories(Section $section, array $tokens): array
    {
        return $section->faqCategories
            ->flatMap(fn ($category): Collection => $category->items
                ->where('is_published', true)
                ->map(fn (FaqItem $item): array => [
                    'id' => $item->public_id,
                    'category' => $category->slug,
                    'category_title' => $this->replaceTokens($category->title, $tokens),
                    'featured' => (bool) $item->is_featured,
                    'sort_order' => (int) $item->sort_order,
                    'question' => $this->replaceTokens($item->question, $tokens),
                    'answer_markdown' => $this->replaceTokens($item->answer_markdown, $tokens),
                ]))
            ->sortBy([
                ['featured', 'desc'],
                ['sort_order', 'asc'],
                ['question', 'asc'],
            ])
            ->values()
            ->map(fn (array $item, int $index): array => $this->presentItem($section, $item, $index))
            ->all();
    }

    /** @param array<string, string> $tokens */
    private function itemsFromBlocks(Section $section, array $tokens): array
    {
        return $section->blocks
            ->filter(fn ($block): bool => $block->type === BlockType::FaqItem)
            ->sortBy('sort_order')
            ->values()
            ->map(fn ($block, int $index): array => $this->presentItem($section, [
                'id' => $block->public_id,
                'category' => null,
                'category_title' => null,
                'featured' => false,
                'sort_order' => (int) $block->sort_order,
                'question' => $this->replaceTokens((string) $block->heading, $tokens),
                'answer_markdown' => $this->replaceTokens((string) $block->body_markdown, $tokens),
            ], $index))
            ->all();
    }

    /** @param array<string, mixed> $item */
    private function presentItem(Section $section, array $item, int $index): array
    {
        $answerMarkdown = (string) $item['answer_markdown'];
        $answerHtml = $this->markdown->render($answerMarkdown, "faq:{$section->public_id}:{$item['id']}");
        $anchor = Str::slug((string) $item['question']) ?: 'vraag';

        return [
            ...$item,
            'anchor' => "faq-{$section->public_id}-{$anchor}-{$index}",
            'panel_id' => "faq-panel-{$section->public_id}-{$index}",
            'answer_html' => $answerHtml,
            'answer_text' => trim(html_entity_decode(strip_tags($answerHtml))),
        ];
    }

    /** @param array<int, array<string, mixed>> $items */
    private function schemaFor(Section $section, array $items): ?string
    {
        if (! $section->faq_schema_enabled || $items === []) {
            return null;
        }

        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $item): array => [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer_text'],
                ],
            ], $items),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: null;
    }

    /** @param array<string, string> $tokens */
    private function replaceTokens(string $value, array $tokens): string
    {
        return strtr($value, $tokens);
    }
}

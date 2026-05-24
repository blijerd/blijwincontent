<?php

namespace App\ViewModels;

use App\Models\Page;
use App\Services\Content\MarkdownRenderService;
use App\Services\Content\NavigationBuilderService;
use App\Services\Downloads\DownloadCatalogBuilderService;
use App\Services\Faq\FaqBuilderService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PageViewModel
{
    public function __construct(
        public readonly Page $page,
        private readonly MarkdownRenderService $markdown,
        private readonly FaqBuilderService $faqBuilder,
        private readonly DownloadCatalogBuilderService $downloadCatalogBuilder,
        private readonly NavigationBuilderService $navigationBuilder,
    ) {}

    public function excerptHtml(): string
    {
        return $this->markdown->render($this->page->excerpt_markdown, "page:{$this->page->public_id}:excerpt");
    }

    /** @return Collection<int, array<string, mixed>> */
    public function sections(): Collection
    {
        return $this->page->sections
            ->where('is_visible', true)
            ->values()
            ->map(fn ($section): array => [
                'model' => $section,
                'intro_html' => $this->markdown->render($section->intro_markdown, "section:{$section->public_id}:intro"),
                'faq' => $this->faqBuilder->build($section),
                'downloads' => $this->downloadCatalogBuilder->build($section),
                'blocks' => $section->blocks->map(fn ($block): array => [
                    'model' => $block,
                    'body_html' => $this->markdown->render($block->body_markdown, "block:{$block->public_id}:body"),
                    'media' => $this->mediaForBlock($block),
                ]),
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    public function mainNavigation(): Collection
    {
        return $this->navigationBuilder->build($this->page->site, $this->page->locale, 'main');
    }

    /** @return Collection<int, array<string, mixed>> */
    public function audienceNavigation(): Collection
    {
        return $this->navigationBuilder->build($this->page->site, $this->page->locale, 'audience');
    }

    /** @return array<string, mixed>|null */
    private function mediaForBlock($block): ?array
    {
        $payload = $block->source_payload ?? [];
        $youtubeId = $this->youtubeId($payload);

        if ($youtubeId) {
            return [
                'type' => 'youtube',
                'youtube_id' => $youtubeId,
                'embed_url' => "https://www.youtube-nocookie.com/embed/{$youtubeId}?autoplay=1&rel=0&modestbranding=1",
                'label' => $payload['mediaLabel'] ?? $payload['label'] ?? $block->heading ?? 'Video afspelen',
            ];
        }

        if ($block->image) {
            return [
                'type' => 'image',
                'url' => $block->image->url(),
                'alt' => $payload['afbeeldingAlt'] ?? $block->image->alt_text ?? $block->heading ?? '',
            ];
        }

        return null;
    }

    /** @param array<string, mixed> $payload */
    private function youtubeId(array $payload): ?string
    {
        $value = $payload['youtubeCode'] ?? $payload['code'] ?? null;

        if (! is_string($value) || trim($value) === '') {
            $mediaType = (string) ($payload['mediaType'] ?? $payload['type'] ?? '');
            $content = $payload['content'] ?? null;

            $value = in_array($mediaType, ['youtube', 'video'], true) && is_string($content) ? $content : null;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (Str::contains($value, ['youtube.com', 'youtu.be'])) {
            parse_str((string) parse_url($value, PHP_URL_QUERY), $query);
            $value = is_string($query['v'] ?? null) ? $query['v'] : basename((string) parse_url($value, PHP_URL_PATH));
        }

        return preg_match('/^[A-Za-z0-9_-]{6,20}$/', $value) === 1 ? $value : null;
    }
}

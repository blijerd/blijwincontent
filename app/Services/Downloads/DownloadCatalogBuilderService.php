<?php

namespace App\Services\Downloads;

use App\Models\DownloadFormat;
use App\Models\DownloadItem;
use App\Models\Section;
use App\Services\Content\MarkdownRenderService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class DownloadCatalogBuilderService
{
    public function __construct(private readonly MarkdownRenderService $markdown) {}

    /** @return array<string, mixed> */
    public function build(Section $section): array
    {
        $categories = $section->downloadCategories
            ->where('is_active', true)
            ->sortBy([['sort_order', 'asc'], ['title', 'asc']])
            ->values();

        $formToken = Str::random(40);
        session()->put(
            config('settings.downloads.secure.form_token_session_key', 'download_secure_form_tokens').'.'.$formToken,
            now()->timestamp,
        );

        return [
            'title' => $section->title,
            'show_category_intro' => (bool) $section->downloads_show_category_intro,
            'secure_enabled' => (bool) config('settings.downloads.secure.enabled', true) && (bool) $section->downloads_secure_enabled,
            'secure_request_url' => url(config('settings.downloads.routes.secure_request', '/downloads/api/request-email')),
            'secure_form_token' => $formToken,
            'honeypot_field' => config('settings.downloads.secure.honeypot_field', 'website_url'),
            'categories' => $categories->map(fn ($category): array => [
                'id' => $category->public_id,
                'title' => $category->title,
                'slug' => $category->slug,
                'intro_html' => $this->markdown->render($category->intro_markdown, "download-category:{$category->public_id}:intro"),
                'items' => $this->items($category->items),
            ])->all(),
        ];
    }

    /** @param Collection<int, DownloadItem> $items */
    private function items(Collection $items): array
    {
        return $items
            ->where('is_active', true)
            ->sortBy([['sort_order', 'asc'], ['title', 'asc']])
            ->values()
            ->map(function (DownloadItem $item): array {
                $formats = $item->formats
                    ->sortBy([['sort_order', 'asc'], ['label', 'asc']])
                    ->values()
                    ->map(fn (DownloadFormat $format): array => $this->format($item, $format))
                    ->all();

                return [
                    'id' => $item->public_id,
                    'title' => $item->title,
                    'preview_html' => $this->markdown->render($item->preview_markdown, "download-item:{$item->public_id}:preview"),
                    'image_url' => $item->previewImage?->url(),
                    'image_alt' => $item->preview_image_alt ?: $item->previewImage?->alt_text ?: $item->title,
                    'image_focus' => $item->preview_image_focus,
                    'primary_format' => $formats[0] ?? null,
                    'alternative_formats' => array_slice($formats, 1),
                    'formats' => $formats,
                ];
            })
            ->all();
    }

    /** @return array<string, mixed> */
    private function format(DownloadItem $item, DownloadFormat $format): array
    {
        return [
            'id' => $format->public_id,
            'label' => $format->label,
            'href' => $this->href($item, $format),
            'is_secure' => (bool) $format->is_secure,
            'is_external' => $format->isExternal(),
            'download' => ! $format->isExternal() && ! $format->is_secure && (bool) config('settings.downloads.presentation.download_local_files', true),
            'target' => $format->isExternal() && (bool) config('settings.downloads.presentation.open_external_in_new_tab', true) ? '_blank' : null,
        ];
    }

    private function href(DownloadItem $item, DownloadFormat $format): string
    {
        if ($format->isExternal() || $this->looksLikePageLink($format->file_path)) {
            return $format->file_path;
        }

        return URL::route('downloads.file', [
            'category' => $item->category->public_id,
            'item' => $item->public_id,
            'format' => $format->public_id,
        ]);
    }

    private function looksLikePageLink(string $path): bool
    {
        return Str::startsWith($path, '/') && ! Str::contains(basename($path), '.');
    }
}

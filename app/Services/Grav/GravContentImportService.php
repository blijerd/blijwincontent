<?php

namespace App\Services\Grav;

use App\Enums\BlockType;
use App\Enums\PageStatus;
use App\Enums\SectionType;
use App\Enums\TemplateType;
use App\Models\Block;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\Section;
use App\Models\Site;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class GravContentImportService
{
    private const SOURCE_SYSTEM = 'grav';

    /** @return array{pages: int, sections: int, blocks: int, media: int} */
    public function import(string $pagesPath, Site $site, string $locale = 'nl'): array
    {
        $root = rtrim(realpath($pagesPath) ?: $pagesPath, DIRECTORY_SEPARATOR);

        if (! is_dir($root)) {
            throw new RuntimeException("Grav pages path does not exist: {$pagesPath}");
        }

        return DB::transaction(function () use ($root, $site, $locale): array {
            $stats = ['pages' => 0, 'sections' => 0, 'blocks' => 0, 'media' => 0];
            $pagesByDirectory = [];

            foreach ($this->pageMarkdownFiles($root) as $file) {
                $page = $this->upsertPage($file, $root, $site, $locale, $pagesByDirectory);
                $pagesByDirectory[$file->getPathname()] = $page;
                $pagesByDirectory[$file->getPath()] = $page;
                $stats['pages']++;

                $stats['media'] += $this->upsertMediaForDirectory($file->getPath(), $root, $this->relativePath($file->getPathname(), $root), $locale);
            }

            foreach ($this->moduleMarkdownFiles($root) as $file) {
                $parent = $this->nearestImportedPage($file->getPath(), $root, $pagesByDirectory);

                if (! $parent) {
                    continue;
                }

                $section = $this->upsertSection($file, $root, $parent);
                $stats['sections']++;
                $stats['blocks'] += $this->upsertBlocks($section, $file, $root);
                $stats['media'] += $this->upsertMediaForDirectory($file->getPath(), $root, $this->relativePath($file->getPathname(), $root), $locale);
            }

            return $stats;
        });
    }

    /** @return list<SplFileInfo> */
    private function pageMarkdownFiles(string $root): array
    {
        return array_values(array_filter(
            $this->markdownFiles($root),
            fn (SplFileInfo $file): bool => ! $this->isModuleDirectory($file->getPath()),
        ));
    }

    /** @return list<SplFileInfo> */
    private function moduleMarkdownFiles(string $root): array
    {
        return array_values(array_filter(
            $this->markdownFiles($root),
            fn (SplFileInfo $file): bool => $this->isModuleDirectory($file->getPath()),
        ));
    }

    /** @return list<SplFileInfo> */
    private function markdownFiles(string $root): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'md') {
                $files[] = $file;
            }
        }

        usort($files, function (SplFileInfo $a, SplFileInfo $b) use ($root): int {
            $aDepth = substr_count($this->relativePath($a->getPathname(), $root), DIRECTORY_SEPARATOR);
            $bDepth = substr_count($this->relativePath($b->getPathname(), $root), DIRECTORY_SEPARATOR);

            return [$aDepth, $this->gravPathSort($a->getPathname(), $b->getPathname(), $root)] <=> [$bDepth, 0];
        });

        return $files;
    }

    private function upsertPage(SplFileInfo $file, string $root, Site $site, string $locale, array $pagesByDirectory): Page
    {
        $document = $this->readMarkdown($file);
        $folder = $file->getPath() === $root ? $file->getBasename('.md') : basename($file->getPath());
        $folderParts = $this->folderParts($folder);
        $parent = $file->getPath() === $root ? null : $this->nearestImportedPage(dirname($file->getPath()), $root, $pagesByDirectory);
        $frontmatter = $document['frontmatter'];

        $page = Page::query()->updateOrCreate(
            [
                'site_id' => $site->id,
                'locale' => $locale,
                'source_system' => self::SOURCE_SYSTEM,
                'source_path' => $this->relativePath($file->getPathname(), $root),
            ],
            [
                'parent_id' => $parent?->id,
                'title' => $this->titleFor($frontmatter, $folderParts['slug']),
                'slug' => $folderParts['slug'],
                'template_type' => $this->pageTemplateType($file->getBasename('.md'), $frontmatter),
                'status' => ($frontmatter['published'] ?? true) === false ? PageStatus::Draft : PageStatus::Published,
                'excerpt_markdown' => trim($document['body']) ?: ($frontmatter['custom']['introtext'] ?? null),
                'published_at' => $this->publishedAt($frontmatter),
                'sort_order' => $folderParts['order'] ?? 9999,
                'seo_title' => $frontmatter['seo-magic']['title'] ?? null,
                'seo_description' => $frontmatter['seo-magic']['description'] ?? ($frontmatter['metadata']['description'] ?? ($frontmatter['meta'] ?? null)),
                'og_title' => $frontmatter['seo-magic']['opengraph']['title'] ?? null,
                'og_description' => $frontmatter['seo-magic']['opengraph']['description'] ?? null,
                'robots_index' => ! ($frontmatter['metadata']['robots'] ?? '') || ! str_contains((string) $frontmatter['metadata']['robots'], 'noindex'),
                'robots_follow' => ! ($frontmatter['metadata']['robots'] ?? '') || ! str_contains((string) $frontmatter['metadata']['robots'], 'nofollow'),
                'source_folder' => $folder,
                'source_template' => $file->getBasename('.md'),
                'source_order_prefix' => $folderParts['order'],
                'source_frontmatter' => $frontmatter,
                'is_routable' => ($frontmatter['routable'] ?? true) !== false,
                'is_visible_in_navigation' => ($frontmatter['visible'] ?? true) !== false,
            ],
        );

        if (trim($document['body']) !== '') {
            $section = Section::query()->updateOrCreate(
                [
                    'page_id' => $page->id,
                    'source_system' => self::SOURCE_SYSTEM,
                    'source_path' => $this->relativePath($file->getPathname(), $root).':body',
                ],
                [
                    'type' => SectionType::RichText,
                    'title' => null,
                    'intro_markdown' => null,
                    'sort_order' => 0,
                    'is_visible' => true,
                    'source_folder' => $folder,
                    'source_template' => $file->getBasename('.md'),
                    'source_order_prefix' => 0,
                    'source_frontmatter' => [],
                ],
            );

            Block::query()->updateOrCreate(
                [
                    'section_id' => $section->id,
                    'source_system' => self::SOURCE_SYSTEM,
                    'source_path' => $this->relativePath($file->getPathname(), $root),
                    'source_key' => 'body',
                ],
                [
                    'type' => BlockType::Text,
                    'sort_order' => 0,
                    'body_markdown' => trim($document['body']),
                    'source_payload' => ['field' => 'markdown_body'],
                ],
            );
        }

        return $page;
    }

    private function upsertSection(SplFileInfo $file, string $root, Page $page): Section
    {
        $document = $this->readMarkdown($file);
        $folder = basename($file->getPath());
        $folderParts = $this->folderParts($folder);
        $frontmatter = $document['frontmatter'];

        return Section::query()->updateOrCreate(
            [
                'page_id' => $page->id,
                'source_system' => self::SOURCE_SYSTEM,
                'source_path' => $this->relativePath($file->getPathname(), $root),
            ],
            [
                'type' => $this->sectionType($file->getBasename('.md')),
                'title' => $frontmatter['titel'] ?? $frontmatter['title'] ?? Str::headline($folderParts['slug']),
                'intro_markdown' => trim($document['body']) ?: ($frontmatter['subtitel'] ?? null),
                'sort_order' => $folderParts['order'] ?? 9999,
                'is_visible' => ($frontmatter['published'] ?? true) !== false,
                'source_folder' => $folder,
                'source_template' => $file->getBasename('.md'),
                'source_order_prefix' => $folderParts['order'],
                'source_frontmatter' => $frontmatter,
            ],
        );
    }

    private function upsertBlocks(Section $section, SplFileInfo $file, string $root): int
    {
        $document = $this->readMarkdown($file);
        $frontmatter = $document['frontmatter'];
        $sourcePath = $this->relativePath($file->getPathname(), $root);
        $count = 0;

        foreach ($this->structuredBlockItems($frontmatter) as $index => $item) {
            Block::query()->updateOrCreate(
                [
                    'section_id' => $section->id,
                    'source_system' => self::SOURCE_SYSTEM,
                    'source_path' => $sourcePath,
                    'source_key' => "item:{$index}",
                ],
                [
                    'type' => BlockType::Text,
                    'sort_order' => ($index + 1) * 10,
                    'heading' => $item['titel'] ?? $item['title'] ?? $item['heading'] ?? null,
                    'subheading' => $item['intro'] ?? $item['eyebrow'] ?? null,
                    'body_markdown' => $item['tekst'] ?? $item['text'] ?? $item['body'] ?? null,
                    'button_label' => $item['ctaTekst'] ?? $item['button_label'] ?? null,
                    'button_url' => $item['ctaUrl'] ?? $item['button_url'] ?? null,
                    'source_payload' => $item,
                ],
            );
            $count++;
        }

        if (trim($document['body']) !== '') {
            Block::query()->updateOrCreate(
                [
                    'section_id' => $section->id,
                    'source_system' => self::SOURCE_SYSTEM,
                    'source_path' => $sourcePath,
                    'source_key' => 'body',
                ],
                [
                    'type' => BlockType::Text,
                    'sort_order' => 0,
                    'body_markdown' => trim($document['body']),
                    'source_payload' => ['field' => 'markdown_body'],
                ],
            );
            $count++;
        }

        return $count;
    }

    /** @return list<array<string, mixed>> */
    private function structuredBlockItems(array $frontmatter): array
    {
        foreach (['kaarten', 'items', 'features', 'downloads', 'reviews', 'waarden', 'blocks'] as $key) {
            if (isset($frontmatter[$key]) && is_array($frontmatter[$key]) && array_is_list($frontmatter[$key])) {
                return array_values(array_filter($frontmatter[$key], 'is_array'));
            }
        }

        return [];
    }

    private function upsertMediaForDirectory(string $directory, string $root, string $sourcePagePath, string $locale): int
    {
        $count = 0;

        foreach (scandir($directory) ?: [] as $filename) {
            $path = $directory.DIRECTORY_SEPARATOR.$filename;

            if (! is_file($path) || str_ends_with($filename, '.md') || str_ends_with($filename, '.yaml')) {
                continue;
            }

            $relativePath = $this->relativePath($path, $root);
            $size = filesize($path) ?: 0;
            $imageSize = @getimagesize($path) ?: null;

            MediaAsset::query()->updateOrCreate(
                [
                    'source_system' => self::SOURCE_SYSTEM,
                    'source_path' => $relativePath,
                ],
                [
                    'disk' => 'local',
                    'path' => $relativePath,
                    'mime_type' => $imageSize['mime'] ?? (mime_content_type($path) ?: 'application/octet-stream'),
                    'original_filename' => $filename,
                    'size' => $size,
                    'width' => $imageSize[0] ?? null,
                    'height' => $imageSize[1] ?? null,
                    'locale' => $locale,
                    'source_page_path' => $sourcePagePath,
                    'source_metadata' => $this->mediaMetadata($path),
                ],
            );

            $count++;
        }

        return $count;
    }

    /** @return array{frontmatter: array<string, mixed>, body: string} */
    private function readMarkdown(SplFileInfo $file): array
    {
        $raw = file_get_contents($file->getPathname()) ?: '';

        if (preg_match('/\A---\R(.*?)\R---\R?(.*)\z/s', $raw, $matches) !== 1) {
            return ['frontmatter' => [], 'body' => trim($raw)];
        }

        $frontmatter = Yaml::parse($matches[1]) ?: [];

        return [
            'frontmatter' => is_array($frontmatter) ? $frontmatter : [],
            'body' => trim($matches[2]),
        ];
    }

    /** @return array{order: int|null, slug: string, is_module: bool} */
    private function folderParts(string $folder): array
    {
        preg_match('/^(?:(\d+)\.)?(.*)$/', $folder, $matches);

        $name = $matches[2] ?? $folder;
        $isModule = str_starts_with($name, '_');
        $name = ltrim($name, '_');

        return [
            'order' => isset($matches[1]) && $matches[1] !== '' ? (int) $matches[1] : null,
            'slug' => Str::slug($name ?: $folder),
            'is_module' => $isModule,
        ];
    }

    private function isModuleDirectory(string $directory): bool
    {
        return $this->folderParts(basename($directory))['is_module'];
    }

    private function nearestImportedPage(string $directory, string $root, array $pagesByDirectory): ?Page
    {
        $current = $directory;

        while ($current !== '' && str_starts_with($current, $root)) {
            if (isset($pagesByDirectory[$current])) {
                return $pagesByDirectory[$current];
            }

            if ($current === $root) {
                return null;
            }

            $current = dirname($current);
        }

        return null;
    }

    private function pageTemplateType(string $template, array $frontmatter): TemplateType
    {
        $template = $frontmatter['template'] ?? $template;

        return match ($template) {
            'blog_record', 'blog_overview', 'blogs' => TemplateType::Blog,
            'downloads-categorie', 'blijwinboekingen-prijslijsten' => TemplateType::Downloads,
            'modular' => TemplateType::LandingPage,
            default => TemplateType::Default,
        };
    }

    private function sectionType(string $template): SectionType
    {
        return match ($template) {
            'hero' => SectionType::Hero,
            '2koloms' => SectionType::TwoColumns,
            'triplets' => SectionType::Triplets,
            'reviews' => SectionType::Reviews,
            'spotlightpanel' => SectionType::SpotlightPanel,
            'veelgestelde-vragen' => SectionType::Faq,
            'videoduo' => SectionType::Video,
            default => SectionType::RichText,
        };
    }

    private function titleFor(array $frontmatter, string $fallback): string
    {
        return (string) ($frontmatter['title'] ?? $frontmatter['titel'] ?? Str::headline($fallback));
    }

    private function publishedAt(array $frontmatter): ?CarbonImmutable
    {
        $value = $frontmatter['publish_date'] ?? $frontmatter['date'] ?? $frontmatter['custom']['datePublished'] ?? null;

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        foreach (['d-m-Y H:i', 'd-m-Y G:i', 'Y-m-d', 'd-m-Y'] as $format) {
            try {
                return CarbonImmutable::createFromFormat($format, $value) ?: null;
            } catch (\Throwable) {
                //
            }
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function mediaMetadata(string $path): array
    {
        $metaPath = $path.'.meta.yaml';

        if (! is_file($metaPath)) {
            return [];
        }

        $metadata = Yaml::parseFile($metaPath) ?: [];

        return is_array($metadata) ? $metadata : [];
    }

    private function relativePath(string $path, string $root): string
    {
        return ltrim(Str::after($path, $root), DIRECTORY_SEPARATOR);
    }

    private function gravPathSort(string $a, string $b, string $root): int
    {
        $aParts = explode(DIRECTORY_SEPARATOR, $this->relativePath($a, $root));
        $bParts = explode(DIRECTORY_SEPARATOR, $this->relativePath($b, $root));

        foreach (range(0, max(count($aParts), count($bParts)) - 1) as $index) {
            $aPart = $aParts[$index] ?? '';
            $bPart = $bParts[$index] ?? '';

            if ($aPart === $bPart) {
                continue;
            }

            $aFolder = $this->folderParts($aPart);
            $bFolder = $this->folderParts($bPart);

            return [$aFolder['order'] ?? 9999, $aFolder['slug']] <=> [$bFolder['order'] ?? 9999, $bFolder['slug']];
        }

        return 0;
    }
}

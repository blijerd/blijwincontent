<?php

namespace App\Actions\Grav;

use App\Models\Page;
use App\Models\Site;
use App\Services\Grav\GravContentImportService;
use RuntimeException;

class ImportDeploymentGravPagesAction
{
    public function __construct(
        private readonly GravContentImportService $importer,
    ) {}

    /**
     * @return array{skipped: bool, reason: string|null, site: Site|null, pages: int, sections: int, blocks: int, media: int, menus: int, menu_items: int}
     */
    public function execute(bool $force = false): array
    {
        $settings = config('settings.grav_page_import', []);

        if (($settings['enabled'] ?? false) !== true) {
            return $this->skipped('disabled');
        }

        $path = $this->resolvePath((string) ($settings['path'] ?? 'database/imports/grav-pages'));

        if (! is_dir($path)) {
            throw new RuntimeException("Deployment Grav pages import path does not exist: {$path}");
        }

        $locale = (string) ($settings['locale'] ?? 'nl');
        $site = $this->resolveSite($settings, $locale);

        if (! $force && $this->hasExistingGravPages($site, $locale)) {
            return $this->skipped('already_imported', $site);
        }

        return [
            'skipped' => false,
            'reason' => null,
            'site' => $site,
            ...$this->importer->import($path, $site, $locale),
        ];
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function resolveSite(array $settings, string $locale): Site
    {
        $siteReference = $settings['site'] ?? null;

        if (is_numeric($siteReference)) {
            return Site::query()->findOrFail((int) $siteReference);
        }

        if (is_string($siteReference) && $siteReference !== '') {
            return Site::query()->firstOrCreate(
                ['domain' => $siteReference],
                [
                    'name' => 'Blijwin',
                    'default_locale' => $locale,
                    'available_locales' => [$locale],
                    'is_active' => true,
                ],
            );
        }

        $site = Site::query()->where('is_active', true)->first();

        if ($site) {
            return $site;
        }

        $domain = (string) ($settings['site_domain'] ?? parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost');

        return Site::query()->create([
            'name' => 'Blijwin',
            'domain' => $domain,
            'default_locale' => $locale,
            'available_locales' => [$locale],
            'is_active' => true,
        ]);
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }

    private function hasExistingGravPages(Site $site, string $locale): bool
    {
        return Page::query()
            ->whereBelongsTo($site)
            ->where('locale', $locale)
            ->where('source_system', 'grav')
            ->exists();
    }

    /**
     * @return array{skipped: true, reason: string, site: Site|null, pages: 0, sections: 0, blocks: 0, media: 0, menus: 0, menu_items: 0}
     */
    private function skipped(string $reason, ?Site $site = null): array
    {
        return [
            'skipped' => true,
            'reason' => $reason,
            'site' => $site,
            'pages' => 0,
            'sections' => 0,
            'blocks' => 0,
            'media' => 0,
            'menus' => 0,
            'menu_items' => 0,
        ];
    }
}

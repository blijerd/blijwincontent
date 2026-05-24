<?php

use App\Actions\Grav\ImportDeploymentGravPagesAction;
use App\Models\Site;
use App\Services\Grav\GravContentImportService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cms:import-grav-pages {path : Path to the Grav user/pages directory} {--site= : Site id or domain} {--locale=nl}', function (GravContentImportService $importer): int {
    $siteOption = $this->option('site');
    $site = $siteOption
        ? Site::query()
            ->whereKey($siteOption)
            ->orWhere('domain', $siteOption)
            ->firstOrFail()
        : Site::query()->firstOrFail();

    $stats = $importer->import(
        (string) $this->argument('path'),
        $site,
        (string) $this->option('locale'),
    );

    $this->info("Imported {$stats['pages']} pages, {$stats['sections']} sections, {$stats['blocks']} blocks, {$stats['media']} media assets, {$stats['menus']} menus and {$stats['menu_items']} menu items.");

    return 0;
})->purpose('Import Grav pages, modules, ordering, frontmatter and media into the CMS');

Artisan::command('cms:import-deployment-grav-pages {--force : Re-import even when Grav pages were imported before}', function (ImportDeploymentGravPagesAction $import): int {
    $stats = $import->execute((bool) $this->option('force'));

    if ($stats['skipped']) {
        $this->info("Skipped deployment Grav page import: {$stats['reason']}.");

        return 0;
    }

    $site = $stats['site'];
    $this->info("Imported {$stats['pages']} pages, {$stats['sections']} sections, {$stats['blocks']} blocks, {$stats['media']} media assets, {$stats['menus']} menus and {$stats['menu_items']} menu items for {$site?->domain}.");

    return 0;
})->purpose('Import the bundled Grav page snapshot during deployment');

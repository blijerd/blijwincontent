<?php

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

    $this->info("Imported {$stats['pages']} pages, {$stats['sections']} sections, {$stats['blocks']} blocks and {$stats['media']} media assets.");

    return 0;
})->purpose('Import Grav pages, modules, ordering, frontmatter and media into the CMS');

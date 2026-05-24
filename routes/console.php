<?php

use App\Actions\Auth\ResetAdminPasswordAction;
use App\Actions\Bookings\RetryPendingBookingRequestsAction;
use App\Actions\Grav\ImportDeploymentGravPagesAction;
use App\Models\Site;
use App\Models\User;
use App\Services\Grav\GravContentImportService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

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

Artisan::command('cms:admin:reset-password {email : Existing admin email address} {--password= : New password, generated when omitted}', function (ResetAdminPasswordAction $resetAdminPassword): int {
    $email = (string) $this->argument('email');
    $password = (string) ($this->option('password') ?: 'A1a'.Str::random(15));

    $validator = Validator::make(
        ['email' => $email, 'password' => $password],
        [
            'email' => ['required', 'email:rfc', 'exists:users,email'],
            'password' => ['required', Password::min(12)->mixedCase()->numbers()],
        ],
    );

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }

        return 1;
    }

    $user = User::query()->where('email', $email)->firstOrFail();
    $resetAdminPassword->execute($user, $password);

    $this->info("Admin password reset for {$email}.");

    if (! $this->option('password')) {
        $this->line("Generated password: {$password}");
    }

    return 0;
})->purpose('Reset an existing CMS admin password without reopening setup');

Artisan::command('bookings:sync-pending {--limit= : Maximum number of local booking requests to retry}', function (RetryPendingBookingRequestsAction $retryPending): int {
    $limit = (int) ($this->option('limit') ?: config('settings.booking_requests.retry_limit', 25));
    $stats = $retryPending->handle($limit);

    $this->info("Retried {$stats['attempted']} booking requests, sent {$stats['sent']}, pending {$stats['pending']}.");

    return 0;
})->purpose('Retry locally stored booking requests against Blijwin OS');

<?php

use App\Http\Controllers\Downloads\DownloadDirectController;
use App\Http\Controllers\Downloads\DownloadSecureController;
use App\Http\Controllers\Downloads\DownloadSecureRequestController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\TrackingCollectController;
use App\Http\Controllers\TrackingConsentController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/setup', [SetupController::class, 'show'])->name('setup.show');
Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');
Route::match(['GET', 'POST'], config('tracking.routes.consent', '/tracking-consent'), TrackingConsentController::class)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('tracking.consent');
Route::post(config('tracking.routes.collect', '/tracking-collect'), TrackingCollectController::class)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('tracking.collect');
Route::get(config('settings.downloads.routes.direct', '/downloads/file').'/{category}/{item}/{format}', DownloadDirectController::class)
    ->name('downloads.file');
Route::post(config('settings.downloads.routes.secure_request', '/downloads/api/request-email'), DownloadSecureRequestController::class)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('downloads.secure-request');
Route::get(config('settings.downloads.routes.secure_delivery', '/downloads/secure').'/{token}', DownloadSecureController::class)
    ->name('downloads.secure');
Route::get('/{path?}', PageController::class)
    ->where('path', '.*')
    ->name('pages.show');

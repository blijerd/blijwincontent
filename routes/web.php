<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TrackingCollectController;
use App\Http\Controllers\TrackingConsentController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::match(['GET', 'POST'], config('tracking.routes.consent', '/tracking-consent'), TrackingConsentController::class)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('tracking.consent');
Route::post(config('tracking.routes.collect', '/tracking-collect'), TrackingCollectController::class)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('tracking.collect');
Route::get('/{path?}', PageController::class)
    ->where('path', '.*')
    ->name('pages.show');

<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/{path?}', PageController::class)
    ->where('path', '.*')
    ->name('pages.show');

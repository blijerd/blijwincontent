<?php

namespace App\Models;

use App\Enums\SearchIndexingMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    /** @use HasFactory<\Database\Factories\SiteFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'default_locale',
        'available_locales',
        'is_active',
        'search_indexing_mode',
    ];

    protected function casts(): array
    {
        return [
            'available_locales' => 'array',
            'is_active' => 'boolean',
            'search_indexing_mode' => SearchIndexingMode::class,
        ];
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function redirects(): HasMany
    {
        return $this->hasMany(Redirect::class);
    }

    public function navigationMenus(): HasMany
    {
        return $this->hasMany(NavigationMenu::class);
    }
}

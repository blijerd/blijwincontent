<?php

namespace App\Models;

use App\Events\ContentChanged;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationMenuItem extends Model
{
    /** @use HasFactory<\Database\Factories\NavigationMenuItemFactory> */
    use HasFactory;

    protected $fillable = [
        'navigation_menu_id',
        'parent_id',
        'page_id',
        'label',
        'url',
        'sort_order',
        'is_visible',
        'opens_in_new_tab',
        'source_system',
        'source_path',
        'source_key',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
            'opens_in_new_tab' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => ContentChanged::dispatch(null));
        static::deleted(fn () => ContentChanged::dispatch(null));
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(NavigationMenu::class, 'navigation_menu_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NavigationMenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(NavigationMenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}

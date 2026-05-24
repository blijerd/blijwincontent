<?php

namespace App\Models;

use App\Events\ContentChanged;
use Database\Factories\DownloadCategoryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DownloadCategory extends Model
{
    /** @use HasFactory<DownloadCategoryFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'site_id',
        'public_id',
        'locale',
        'title',
        'slug',
        'intro_markdown',
        'sort_order',
        'is_active',
    ];

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn (DownloadCategory $category) => $category->dispatchContentChanged());
        static::deleted(fn (DownloadCategory $category) => $category->dispatchContentChanged());
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DownloadItem::class)->orderBy('sort_order');
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class)->withTimestamps();
    }

    private function dispatchContentChanged(): void
    {
        $this->sections()->with('page')->get()->each(
            fn (Section $section) => ContentChanged::dispatch($section->page),
        );
    }
}

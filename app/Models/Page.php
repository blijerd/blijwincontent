<?php

namespace App\Models;

use App\Enums\PageStatus;
use App\Enums\TemplateType;
use App\Actions\Content\PreparePageForSaveAction;
use App\Events\ContentChanged;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    /** @use HasFactory<\Database\Factories\PageFactory> */
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'site_id',
        'parent_id',
        'public_id',
        'translation_group_id',
        'locale',
        'title',
        'slug',
        'full_path',
        'template_type',
        'status',
        'excerpt_markdown',
        'published_at',
        'sort_order',
        'seo_title',
        'seo_description',
        'og_title',
        'og_description',
        'canonical_url',
        'robots_index',
        'robots_follow',
    ];

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    protected function casts(): array
    {
        return [
            'template_type' => TemplateType::class,
            'status' => PageStatus::class,
            'published_at' => 'datetime',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(fn (Page $page) => app(PreparePageForSaveAction::class)->execute($page));
        static::saved(fn (Page $page) => ContentChanged::dispatch($page));
        static::deleted(fn (Page $page) => ContentChanged::dispatch($page));
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('sort_order');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('sort_order');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Page::class, 'translation_group_id', 'translation_group_id')
            ->whereKeyNot($this->getKey());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', PageStatus::Published)
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }
}

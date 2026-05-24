<?php

namespace App\Models;

use App\Enums\SectionType;
use App\Events\ContentChanged;
use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    /** @use HasFactory<SectionFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'page_id',
        'public_id',
        'type',
        'title',
        'intro_markdown',
        'faq_keyword',
        'faq_searchable',
        'faq_categories_enabled',
        'faq_schema_enabled',
        'faq_expand_first',
        'faq_allow_multiple_open',
        'faq_initial_limit',
        'faq_cta_label',
        'faq_cta_url',
        'downloads_show_category_intro',
        'downloads_secure_enabled',
        'sort_order',
        'is_visible',
    ];

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    protected function casts(): array
    {
        return [
            'type' => SectionType::class,
            'is_visible' => 'boolean',
            'faq_searchable' => 'boolean',
            'faq_categories_enabled' => 'boolean',
            'faq_schema_enabled' => 'boolean',
            'faq_expand_first' => 'boolean',
            'faq_allow_multiple_open' => 'boolean',
            'faq_initial_limit' => 'integer',
            'downloads_show_category_intro' => 'boolean',
            'downloads_secure_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn (Section $section) => ContentChanged::dispatch($section->page));
        static::deleted(fn (Section $section) => ContentChanged::dispatch($section->page));
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class)->orderBy('sort_order');
    }

    public function faqCategories(): BelongsToMany
    {
        return $this->belongsToMany(FaqCategory::class)->withTimestamps();
    }

    public function downloadCategories(): BelongsToMany
    {
        return $this->belongsToMany(DownloadCategory::class)->withTimestamps();
    }
}

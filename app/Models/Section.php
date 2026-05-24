<?php

namespace App\Models;

use App\Enums\SectionType;
use App\Events\ContentChanged;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    /** @use HasFactory<\Database\Factories\SectionFactory> */
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'page_id',
        'public_id',
        'type',
        'title',
        'intro_markdown',
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
}

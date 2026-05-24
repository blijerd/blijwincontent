<?php

namespace App\Models;

use App\Events\ContentChanged;
use Database\Factories\FaqItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaqItem extends Model
{
    /** @use HasFactory<FaqItemFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'faq_category_id',
        'public_id',
        'question',
        'answer_markdown',
        'is_featured',
        'is_published',
        'sort_order',
    ];

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn (FaqItem $item) => $item->category?->sections->each(
            fn (Section $section) => ContentChanged::dispatch($section->page),
        ));
        static::deleted(fn (FaqItem $item) => $item->category?->sections->each(
            fn (Section $section) => ContentChanged::dispatch($section->page),
        ));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }
}

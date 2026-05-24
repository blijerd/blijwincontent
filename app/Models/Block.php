<?php

namespace App\Models;

use App\Enums\BlockType;
use App\Events\ContentChanged;
use Database\Factories\BlockFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    /** @use HasFactory<BlockFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'section_id',
        'public_id',
        'type',
        'sort_order',
        'heading',
        'subheading',
        'body_markdown',
        'button_label',
        'button_url',
        'image_id',
        'source_system',
        'source_path',
        'source_key',
        'source_payload',
    ];

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    protected function casts(): array
    {
        return [
            'type' => BlockType::class,
            'source_payload' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn (Block $block) => ContentChanged::dispatch($block->section?->page));
        static::deleted(fn (Block $block) => ContentChanged::dispatch($block->section?->page));
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'image_id');
    }
}

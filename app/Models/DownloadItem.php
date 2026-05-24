<?php

namespace App\Models;

use App\Events\ContentChanged;
use Database\Factories\DownloadItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DownloadItem extends Model
{
    /** @use HasFactory<DownloadItemFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'download_category_id',
        'public_id',
        'title',
        'preview_markdown',
        'preview_image_id',
        'preview_image_alt',
        'preview_image_focus',
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
        static::saved(fn (DownloadItem $item) => $item->dispatchContentChanged());
        static::deleted(fn (DownloadItem $item) => $item->dispatchContentChanged());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DownloadCategory::class, 'download_category_id');
    }

    public function previewImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'preview_image_id');
    }

    public function formats(): HasMany
    {
        return $this->hasMany(DownloadFormat::class)->orderBy('sort_order');
    }

    private function dispatchContentChanged(): void
    {
        $this->category?->sections()->with('page')->get()->each(
            fn (Section $section) => ContentChanged::dispatch($section->page),
        );
    }
}

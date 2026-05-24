<?php

namespace App\Models;

use App\Events\ContentChanged;
use Database\Factories\DownloadFormatFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DownloadFormat extends Model
{
    /** @use HasFactory<DownloadFormatFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'download_item_id',
        'public_id',
        'label',
        'file_path',
        'is_secure',
        'sort_order',
    ];

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    protected function casts(): array
    {
        return [
            'is_secure' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn (DownloadFormat $format) => $format->dispatchContentChanged());
        static::deleted(fn (DownloadFormat $format) => $format->dispatchContentChanged());
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(DownloadItem::class, 'download_item_id');
    }

    public function isExternal(): bool
    {
        return Str::contains($this->file_path, '://') || Str::startsWith($this->file_path, ['mailto:', 'tel:']);
    }

    private function dispatchContentChanged(): void
    {
        $this->item?->category?->sections()->with('page')->get()->each(
            fn (Section $section) => ContentChanged::dispatch($section->page),
        );
    }
}

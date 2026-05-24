<?php

namespace App\Models;

use Database\Factories\MediaAssetFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    /** @use HasFactory<MediaAssetFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'public_id',
        'disk',
        'path',
        'mime_type',
        'original_filename',
        'size',
        'width',
        'height',
        'alt_text',
        'locale',
        'source_system',
        'source_path',
        'source_page_path',
        'source_metadata',
    ];

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    protected function casts(): array
    {
        return [
            'source_metadata' => 'array',
        ];
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'image_id');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    /** @use HasFactory<\Database\Factories\MediaAssetFactory> */
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
    ];

    public function uniqueIds(): array
    {
        return ['public_id'];
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

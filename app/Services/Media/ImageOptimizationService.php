<?php

namespace App\Services\Media;

use App\Models\MediaAsset;

class ImageOptimizationService
{
    /** @return array<string, string|int|null> */
    public function responsiveAttributes(MediaAsset $asset): array
    {
        return [
            'src' => $asset->url(),
            'alt' => $asset->alt_text,
            'width' => $asset->width,
            'height' => $asset->height,
            'loading' => 'lazy',
        ];
    }
}

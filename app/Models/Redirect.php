<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redirect extends Model
{
    /** @use HasFactory<\Database\Factories\RedirectFactory> */
    use HasFactory;

    protected $fillable = [
        'site_id',
        'source_path',
        'target_url',
        'status_code',
        'locale',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}

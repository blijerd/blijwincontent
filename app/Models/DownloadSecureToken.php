<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadSecureToken extends Model
{
    protected $fillable = [
        'download_format_id',
        'token',
        'first_name',
        'email',
        'expires_at',
        'access_count',
        'last_accessed_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'access_count' => 'integer',
        ];
    }

    public function format(): BelongsTo
    {
        return $this->belongsTo(DownloadFormat::class, 'download_format_id');
    }
}

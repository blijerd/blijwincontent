<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadMailLog extends Model
{
    protected $fillable = [
        'download_format_id',
        'download_secure_token_id',
        'first_name',
        'email',
        'status',
        'message',
        'ip_address',
    ];

    public function format(): BelongsTo
    {
        return $this->belongsTo(DownloadFormat::class, 'download_format_id');
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(DownloadSecureToken::class, 'download_secure_token_id');
    }
}

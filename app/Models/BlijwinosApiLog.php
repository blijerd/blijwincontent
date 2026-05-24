<?php

namespace App\Models;

use Database\Factories\BlijwinosApiLogFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlijwinosApiLog extends Model
{
    /** @use HasFactory<BlijwinosApiLogFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'public_id',
        'direction',
        'method',
        'endpoint',
        'status_code',
        'successful',
        'duration_ms',
        'request_id',
        'error_class',
        'error_message',
        'metadata',
    ];

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'metadata' => 'array',
        ];
    }
}

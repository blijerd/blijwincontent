<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TrackingConsentDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'tracking_visitor_id',
        'client_identifier',
        'source',
        'necessary_granted',
        'analytics_granted',
        'marketing_granted',
        'storage_mode',
        'request_ip',
        'user_agent',
        'decided_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (TrackingConsentDecision $decision): void {
            $decision->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'necessary_granted' => 'bool',
            'analytics_granted' => 'bool',
            'marketing_granted' => 'bool',
            'decided_at' => 'datetime',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(TrackingVisitor::class, 'tracking_visitor_id');
    }
}

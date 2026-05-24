<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TrackingPageVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'tracking_visitor_id',
        'tracking_session_id',
        'identifier',
        'slug',
        'path',
        'url',
        'title',
        'referrer',
        'device',
        'landing',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'gclid',
        'fbclid',
        'heartbeat_count',
        'estimated_seconds',
        'started_at',
        'last_seen_at',
        'ended_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (TrackingPageVisit $pageVisit): void {
            $pageVisit->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'landing' => 'bool',
            'started_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(TrackingVisitor::class, 'tracking_visitor_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TrackingSession::class, 'tracking_session_id');
    }

    public function contactAttempts(): HasMany
    {
        return $this->hasMany(TrackingContactAttempt::class);
    }
}

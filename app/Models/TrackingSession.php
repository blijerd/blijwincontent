<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TrackingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'tracking_visitor_id',
        'identifier',
        'storage_mode',
        'first_seen_at',
        'last_seen_at',
        'pageview_count',
        'contact_attempt_count',
    ];

    protected static function booted(): void
    {
        static::creating(function (TrackingSession $session): void {
            $session->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(TrackingVisitor::class, 'tracking_visitor_id');
    }

    public function pageVisits(): HasMany
    {
        return $this->hasMany(TrackingPageVisit::class);
    }

    public function contactAttempts(): HasMany
    {
        return $this->hasMany(TrackingContactAttempt::class);
    }
}

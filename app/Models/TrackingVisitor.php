<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class TrackingVisitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'identifier',
        'first_seen_at',
        'last_seen_at',
        'first_referrer',
        'first_device',
        'first_utm_source',
        'first_utm_medium',
        'first_utm_campaign',
        'first_utm_content',
        'first_utm_term',
        'first_gclid',
        'first_fbclid',
        'pageview_count',
        'contact_attempt_count',
    ];

    protected static function booted(): void
    {
        static::creating(function (TrackingVisitor $visitor): void {
            $visitor->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TrackingSession::class);
    }

    public function pageVisits(): HasMany
    {
        return $this->hasMany(TrackingPageVisit::class);
    }

    public function contactAttempts(): HasMany
    {
        return $this->hasMany(TrackingContactAttempt::class);
    }

    public function consentDecisions(): HasMany
    {
        return $this->hasMany(TrackingConsentDecision::class);
    }

    public function latestConsentDecision(): HasOne
    {
        return $this->hasOne(TrackingConsentDecision::class)->latestOfMany('decided_at');
    }
}

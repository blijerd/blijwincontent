<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TrackingContactAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'tracking_visitor_id',
        'tracking_session_id',
        'tracking_page_visit_id',
        'event_type',
        'contact_type',
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
        'href',
        'link_text',
        'form_action',
        'form_id',
        'form_name',
        'form_method',
        'occurred_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (TrackingContactAttempt $attempt): void {
            $attempt->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'landing' => 'bool',
            'occurred_at' => 'datetime',
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

    public function pageVisit(): BelongsTo
    {
        return $this->belongsTo(TrackingPageVisit::class, 'tracking_page_visit_id');
    }
}

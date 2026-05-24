<?php

namespace App\Models;

use App\Enums\BookingRequestAvailabilityStatus;
use App\Enums\BookingRequestEmailConfirmationStatus;
use App\Enums\BookingRequestSyncStatus;
use Database\Factories\BookingRequestFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingRequest extends Model
{
    /** @use HasFactory<BookingRequestFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'public_id',
        'sync_status',
        'availability_status',
        'email_confirmation_status',
        'blijwinos_public_id',
        'event_type',
        'package_slug',
        'requested_date',
        'requested_start_time',
        'alternative_date',
        'alternative_start_time',
        'duration_minutes',
        'guest_count',
        'location_name',
        'address',
        'postal_code',
        'city',
        'contact_first_name',
        'contact_last_name',
        'organization',
        'email',
        'phone',
        'notes_markdown',
        'privacy_accepted',
        'payload',
        'blijwinos_response',
        'sync_attempts',
        'last_attempted_at',
        'sent_at',
        'last_error',
        'source_url',
        'ip_address',
        'user_agent',
    ];

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    protected function casts(): array
    {
        return [
            'sync_status' => BookingRequestSyncStatus::class,
            'availability_status' => BookingRequestAvailabilityStatus::class,
            'email_confirmation_status' => BookingRequestEmailConfirmationStatus::class,
            'requested_date' => 'date',
            'alternative_date' => 'date',
            'privacy_accepted' => 'boolean',
            'payload' => 'array',
            'blijwinos_response' => 'array',
            'last_attempted_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }
}

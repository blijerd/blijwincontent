<?php

namespace App\Actions\Bookings;

use App\Enums\BookingRequestSyncStatus;
use App\Models\BookingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SubmitBookingRequestAction
{
    public function __construct(private readonly SyncBookingRequestToBlijwinosAction $sync) {}

    /**
     * @param array<string, mixed> $data
     * @return array{booking_request: BookingRequest, submitted_to_blijwinos: bool}
     */
    public function handle(array $data, Request $request): array
    {
        $payload = $this->payload($data, $request);

        $bookingRequest = BookingRequest::query()->create([
            ...Arr::only($data, [
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
            ]),
            'sync_status' => BookingRequestSyncStatus::Pending,
            'payload' => $payload,
            'source_url' => $request->headers->get('referer') ?: $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return [
            'booking_request' => $this->sync->handle($bookingRequest),
            'submitted_to_blijwinos' => $bookingRequest->fresh()?->sync_status === BookingRequestSyncStatus::Sent,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function payload(array $data, Request $request): array
    {
        return [
            'source' => 'blijwincontent',
            'source_url' => $request->headers->get('referer') ?: $request->fullUrl(),
            'event' => [
                'type' => $data['event_type'],
                'package_slug' => $data['package_slug'] ?? null,
                'requested_date' => $data['requested_date'],
                'requested_start_time' => $data['requested_start_time'] ?? null,
                'alternative_date' => $data['alternative_date'] ?? null,
                'alternative_start_time' => $data['alternative_start_time'] ?? null,
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'guest_count' => $data['guest_count'] ?? null,
            ],
            'location' => [
                'name' => $data['location_name'] ?? null,
                'address' => $data['address'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'city' => $data['city'],
            ],
            'contact' => [
                'first_name' => $data['contact_first_name'],
                'last_name' => $data['contact_last_name'] ?? null,
                'organization' => $data['organization'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ],
            'notes_markdown' => $data['notes_markdown'] ?? null,
            'privacy_accepted' => (bool) ($data['privacy_accepted'] ?? false),
        ];
    }
}

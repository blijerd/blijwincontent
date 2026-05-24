<?php

namespace App\Actions\Bookings;

use App\Actions\Blijwinos\WriteBlijwinosBookingRequestAction;
use App\Enums\BookingRequestAvailabilityStatus;
use App\Enums\BookingRequestEmailConfirmationStatus;
use App\Enums\BookingRequestSyncStatus;
use App\Models\BookingRequest;

class SyncBookingRequestToBlijwinosAction
{
    public function __construct(private readonly WriteBlijwinosBookingRequestAction $write) {}

    public function handle(BookingRequest $bookingRequest): BookingRequest
    {
        $bookingRequest->forceFill([
            'sync_attempts' => $bookingRequest->sync_attempts + 1,
            'last_attempted_at' => now(),
        ])->save();

        try {
            $response = $this->write->handle([
                ...$bookingRequest->payload,
                'public_id' => $bookingRequest->public_id,
            ]);

            $bookingRequest->forceFill([
                'sync_status' => BookingRequestSyncStatus::Sent,
                'availability_status' => $this->availabilityStatus($response),
                'email_confirmation_status' => $this->emailConfirmationStatus($response),
                'blijwinos_public_id' => data_get($response, 'data.public_id'),
                'blijwinos_response' => $response,
                'sent_at' => now(),
                'last_error' => null,
            ])->save();
        } catch (\Throwable $exception) {
            $bookingRequest->forceFill([
                'sync_status' => BookingRequestSyncStatus::Pending,
                'last_error' => $exception->getMessage(),
            ])->save();
        }

        return $bookingRequest->refresh();
    }

    /** @param array<string, mixed> $response */
    private function availabilityStatus(array $response): BookingRequestAvailabilityStatus
    {
        $status = (string) (
            data_get($response, 'data.availability_status')
            ?? data_get($response, 'availability_status')
            ?? data_get($response, 'data.availability.status')
            ?? ''
        );

        return match ($status) {
            'available', 'is_available' => BookingRequestAvailabilityStatus::Available,
            'unavailable', 'not_available' => BookingRequestAvailabilityStatus::Unavailable,
            'alternative_requested', 'proposal_requested', 'propose_alternative' => BookingRequestAvailabilityStatus::AlternativeRequested,
            default => BookingRequestAvailabilityStatus::Unknown,
        };
    }

    /** @param array<string, mixed> $response */
    private function emailConfirmationStatus(array $response): BookingRequestEmailConfirmationStatus
    {
        $status = (string) (
            data_get($response, 'data.email_confirmation_status')
            ?? data_get($response, 'email_confirmation_status')
            ?? ''
        );

        if ($status === 'confirmed') {
            return BookingRequestEmailConfirmationStatus::Confirmed;
        }

        if ($status === 'not_required' || data_get($response, 'data.email_confirmation_required') === false) {
            return BookingRequestEmailConfirmationStatus::NotRequired;
        }

        return BookingRequestEmailConfirmationStatus::Pending;
    }
}

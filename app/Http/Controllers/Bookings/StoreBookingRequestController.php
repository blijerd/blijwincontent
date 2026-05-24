<?php

namespace App\Http\Controllers\Bookings;

use App\Actions\Bookings\SubmitBookingRequestAction;
use App\Enums\BookingRequestAvailabilityStatus;
use App\Enums\BookingRequestSyncStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequestRequest;
use Illuminate\Http\JsonResponse;

class StoreBookingRequestController extends Controller
{
    public function __invoke(StoreBookingRequestRequest $request, SubmitBookingRequestAction $submit): JsonResponse
    {
        $result = $submit->handle($request->validated(), $request);
        $bookingRequest = $result['booking_request'];

        return response()->json([
            'public_id' => $bookingRequest->public_id,
            'sync_status' => $bookingRequest->sync_status->value,
            'availability_status' => $bookingRequest->availability_status->value,
            'email_confirmation_status' => $bookingRequest->email_confirmation_status->value,
            'screen' => $this->screen($bookingRequest->sync_status, $bookingRequest->availability_status),
            'message' => $bookingRequest->sync_status === BookingRequestSyncStatus::Sent
                ? 'Je aanvraag is ontvangen. Bevestig je e-mailadres om de aanvraag af te ronden.'
                : 'Je aanvraag is lokaal opgeslagen. We sturen hem automatisch door zodra de koppeling weer beschikbaar is.',
        ], $bookingRequest->sync_status === BookingRequestSyncStatus::Sent ? 201 : 202);
    }

    private function screen(BookingRequestSyncStatus $syncStatus, BookingRequestAvailabilityStatus $availabilityStatus): string
    {
        if ($syncStatus !== BookingRequestSyncStatus::Sent) {
            return 'queued';
        }

        return match ($availabilityStatus) {
            BookingRequestAvailabilityStatus::Available => 'available',
            BookingRequestAvailabilityStatus::Unavailable,
            BookingRequestAvailabilityStatus::AlternativeRequested => 'propose_alternative',
            BookingRequestAvailabilityStatus::Unknown => 'confirm_email',
        };
    }
}

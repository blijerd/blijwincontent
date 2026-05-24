<?php

namespace App\Actions\Bookings;

use App\Enums\BookingRequestSyncStatus;
use App\Models\BookingRequest;

class RetryPendingBookingRequestsAction
{
    public function __construct(private readonly SyncBookingRequestToBlijwinosAction $sync) {}

    /** @return array{attempted: int, sent: int, pending: int} */
    public function handle(int $limit = 25): array
    {
        $attempted = 0;
        $sent = 0;

        BookingRequest::query()
            ->where('sync_status', BookingRequestSyncStatus::Pending)
            ->orderBy('created_at')
            ->limit(max(1, $limit))
            ->get()
            ->each(function (BookingRequest $bookingRequest) use (&$attempted, &$sent): void {
                $attempted++;

                $synced = $this->sync->handle($bookingRequest);

                if ($synced->sync_status === BookingRequestSyncStatus::Sent) {
                    $sent++;
                }
            });

        return [
            'attempted' => $attempted,
            'sent' => $sent,
            'pending' => BookingRequest::query()->where('sync_status', BookingRequestSyncStatus::Pending)->count(),
        ];
    }
}

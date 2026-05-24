<?php

namespace Database\Factories;

use App\Enums\BookingRequestAvailabilityStatus;
use App\Enums\BookingRequestEmailConfirmationStatus;
use App\Enums\BookingRequestSyncStatus;
use App\Models\BookingRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BookingRequest> */
class BookingRequestFactory extends Factory
{
    protected $model = BookingRequest::class;

    public function definition(): array
    {
        $payload = [
            'event_type' => 'kinderdisco',
            'requested_date' => now()->addMonth()->toDateString(),
            'contact_first_name' => fake()->firstName(),
            'email' => fake()->safeEmail(),
        ];

        return [
            'sync_status' => BookingRequestSyncStatus::Pending,
            'availability_status' => BookingRequestAvailabilityStatus::Unknown,
            'email_confirmation_status' => BookingRequestEmailConfirmationStatus::Pending,
            'event_type' => $payload['event_type'],
            'package_slug' => 'basis',
            'requested_date' => $payload['requested_date'],
            'requested_start_time' => '14:00',
            'contact_first_name' => $payload['contact_first_name'],
            'email' => $payload['email'],
            'privacy_accepted' => true,
            'payload' => $payload,
            'source_url' => 'https://www.blijwin.nl/boeken/aanvraag',
            'ip_address' => fake()->ipv4(),
        ];
    }
}

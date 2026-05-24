<?php

namespace Tests\Feature\Bookings;

use App\Actions\Bookings\RetryPendingBookingRequestsAction;
use App\Enums\BookingRequestAvailabilityStatus;
use App\Enums\BookingRequestSyncStatus;
use App\Models\BookingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookingRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_booking_request_is_sent_to_blijwinos(): void
    {
        config()->set('settings.blijwinos.enabled', true);
        config()->set('settings.blijwinos.base_url', 'https://blijwinos.test');
        config()->set('settings.blijwinos.write_hmac_secret', 'test-write-secret');

        Http::fake([
            'https://blijwinos.test/api/blijwinboekingen/aanvragen' => Http::response([
                'data' => [
                    'public_id' => 'blijwinos-request-id',
                    'availability_status' => 'available',
                    'email_confirmation_status' => 'pending',
                ],
            ], 201),
        ]);

        $response = $this->postJson(route('booking-requests.store'), $this->validPayload());

        $response
            ->assertCreated()
            ->assertJsonPath('sync_status', 'sent')
            ->assertJsonPath('availability_status', 'available')
            ->assertJsonPath('screen', 'available');

        $this->assertDatabaseHas('booking_requests', [
            'email' => 'ouder@example.test',
            'sync_status' => BookingRequestSyncStatus::Sent->value,
            'availability_status' => BookingRequestAvailabilityStatus::Available->value,
            'blijwinos_public_id' => 'blijwinos-request-id',
        ]);
    }

    public function test_public_booking_request_falls_back_to_local_storage_when_blijwinos_fails(): void
    {
        config()->set('settings.blijwinos.enabled', true);
        config()->set('settings.blijwinos.base_url', 'https://blijwinos.test');
        config()->set('settings.blijwinos.write_hmac_secret', null);

        $response = $this->postJson(route('booking-requests.store'), $this->validPayload());

        $response
            ->assertAccepted()
            ->assertJsonPath('sync_status', 'pending')
            ->assertJsonPath('screen', 'queued');

        $bookingRequest = BookingRequest::query()->firstOrFail();

        $this->assertSame(BookingRequestSyncStatus::Pending, $bookingRequest->sync_status);
        $this->assertNotNull($bookingRequest->last_error);
        $this->assertSame(1, $bookingRequest->sync_attempts);
    }

    public function test_pending_local_booking_requests_can_be_retried(): void
    {
        config()->set('settings.blijwinos.enabled', true);
        config()->set('settings.blijwinos.base_url', 'https://blijwinos.test');
        config()->set('settings.blijwinos.write_hmac_secret', 'test-write-secret');

        $bookingRequest = BookingRequest::factory()->create();

        Http::fake([
            'https://blijwinos.test/api/blijwinboekingen/aanvragen' => Http::response([
                'data' => ['public_id' => 'retried-request-id'],
            ], 201),
        ]);

        $stats = app(RetryPendingBookingRequestsAction::class)->handle();

        $this->assertSame(['attempted' => 1, 'sent' => 1, 'pending' => 0], $stats);
        $this->assertSame(BookingRequestSyncStatus::Sent, $bookingRequest->refresh()->sync_status);
        $this->assertSame('retried-request-id', $bookingRequest->blijwinos_public_id);
    }

    /** @return array<string, mixed> */
    private function validPayload(): array
    {
        return [
            'event_type' => 'kinderdisco',
            'package_slug' => 'basis',
            'requested_date' => now()->addMonth()->toDateString(),
            'requested_start_time' => '14:00',
            'alternative_date' => now()->addMonth()->addDay()->toDateString(),
            'alternative_start_time' => '15:00',
            'duration_minutes' => 120,
            'guest_count' => 35,
            'location_name' => 'Clubhuis',
            'address' => 'Feeststraat 1',
            'postal_code' => '1234 AB',
            'city' => 'Leiden',
            'contact_first_name' => 'Sanne',
            'contact_last_name' => 'Jansen',
            'organization' => 'Basisschool Test',
            'email' => 'ouder@example.test',
            'phone' => '0712340456',
            'notes_markdown' => 'Graag een vrolijke show.',
            'privacy_accepted' => '1',
        ];
    }
}

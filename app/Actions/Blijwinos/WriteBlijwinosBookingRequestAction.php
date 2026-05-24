<?php

namespace App\Actions\Blijwinos;

use App\Services\Blijwinos\BlijwinosApiClient;

class WriteBlijwinosBookingRequestAction
{
    public function __construct(private readonly BlijwinosApiClient $client) {}

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(array $payload): array
    {
        return $this->client->submitBookingRequest($payload);
    }
}

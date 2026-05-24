<?php

namespace App\Actions\Blijwinos;

use App\Services\Blijwinos\BlijwinosApiClient;

class ReadBlijwinosCatalogAction
{
    public function __construct(private readonly BlijwinosApiClient $client) {}

    /** @return array<string, mixed> */
    public function handle(?string $type = null, ?string $package = null, bool $fresh = false): array
    {
        return $this->client->catalog($type, $package, $fresh);
    }
}

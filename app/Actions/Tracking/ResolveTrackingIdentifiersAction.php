<?php

namespace App\Actions\Tracking;

use App\Services\Tracking\TrackingIdentifierService;
use Illuminate\Http\Request;

class ResolveTrackingIdentifiersAction
{
    public function __construct(private readonly TrackingIdentifierService $identifierService)
    {
    }

    /**
     * @param  array<string, mixed>  $consentState
     * @return array{visitor_id:string,session_id:string,storage_mode:string}
     */
    public function handle(Request $request, array $consentState, ?string $visitorId = null, ?string $sessionId = null): array
    {
        return $this->identifierService->resolve($request, $consentState, $visitorId, $sessionId);
    }
}

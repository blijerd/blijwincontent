<?php

namespace App\Actions\Tracking;

use App\Services\Tracking\TrackingConsentService;
use Illuminate\Http\Request;

class SaveTrackingConsentAction
{
    public function __construct(private readonly TrackingConsentService $consentService)
    {
    }

    /**
     * @param  array<string, mixed>  $categories
     * @return array<string, mixed>
     */
    public function handle(Request $request, array $categories, string $source, ?string $visitorIdentifier = null): array
    {
        return $this->consentService->saveConsent($request, $categories, $source, $visitorIdentifier);
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\Tracking\ResolveTrackingIdentifiersAction;
use App\Actions\Tracking\SaveTrackingConsentAction;
use App\Services\Tracking\TrackingConsentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingConsentController extends Controller
{
    public function __invoke(
        Request $request,
        TrackingConsentService $consentService,
        ResolveTrackingIdentifiersAction $resolveIdentifiers,
        SaveTrackingConsentAction $saveConsent,
    ): JsonResponse {
        if ($request->isMethod('get')) {
            $consentState = $consentService->getConsentState($request);

            return response()->json([
                'ok' => true,
                'consent' => $consentService->frontendState($request),
                'identifiers' => $resolveIdentifiers->handle($request, $consentState),
                'granted_scripts' => [],
            ]);
        }

        $payload = $request->json()->all() ?: $request->all();
        $existingConsent = $consentService->getConsentState($request);
        $currentIdentifiers = $resolveIdentifiers->handle($request, $existingConsent);
        $consentState = $saveConsent->handle(
            $request,
            is_array($payload['categories'] ?? null) ? $payload['categories'] : [],
            (string) ($payload['source'] ?? 'update_preferences'),
            $currentIdentifiers['visitor_id'],
        );

        return response()->json([
            'ok' => true,
            'consent' => $consentService->frontendState($request),
            'identifiers' => $resolveIdentifiers->handle($request, $consentState),
            'granted_scripts' => [],
        ]);
    }
}

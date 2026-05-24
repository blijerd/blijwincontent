<?php

namespace App\Http\Controllers;

use App\Actions\Tracking\RecordTrackingEventAction;
use App\Services\Tracking\TrackingConsentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingCollectController extends Controller
{
    public function __invoke(
        Request $request,
        RecordTrackingEventAction $recordTrackingEvent,
        TrackingConsentService $consentService,
    ): JsonResponse {
        $payload = $request->json()->all() ?: $request->all();
        $result = $recordTrackingEvent->handle(
            $request,
            is_array($payload) ? $payload : [],
            $consentService->getConsentState($request),
        );

        return response()->json($result['body'], $result['status']);
    }
}

<?php

namespace App\Services\Tracking;

use App\Models\TrackingSession;
use App\Models\TrackingVisitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class TrackingIdentifierService
{
    public function __construct(private readonly TrackingConsentService $consentService)
    {
    }

    /**
     * @param  array<string, mixed>  $consentState
     * @return array{visitor_id:string,session_id:string,storage_mode:string}
     */
    public function resolve(Request $request, array $consentState, ?string $providedVisitorId = null, ?string $providedSessionId = null): array
    {
        $storageMode = $this->consentService->shouldPersistIdentifiersInCookies($consentState) ? 'cookie' : 'server_session';
        $visitorId = $this->sanitizeIdentifier($providedVisitorId)
            ?: $this->sanitizeIdentifier($request->cookie((string) config('tracking.cookies.visitor')))
            ?: $this->sanitizeIdentifier($request->session()->get('tracking.identifiers.visitor_id'))
            ?: $this->generateIdentifier('v');
        $sessionId = $this->sanitizeIdentifier($providedSessionId)
            ?: $this->sanitizeIdentifier($request->cookie((string) config('tracking.cookies.session')))
            ?: $this->sanitizeIdentifier($request->session()->get('tracking.identifiers.session_id'))
            ?: $this->generateIdentifier('s');

        $now = now();
        $visitor = TrackingVisitor::query()->firstOrCreate(
            ['identifier' => $visitorId],
            [
                'first_seen_at' => $now,
                'last_seen_at' => $now,
            ],
        );

        /** @var TrackingSession $session */
        $session = TrackingSession::query()->firstOrCreate(
            ['identifier' => $sessionId],
            [
                'tracking_visitor_id' => $visitor->getKey(),
                'storage_mode' => $storageMode,
                'first_seen_at' => $now,
                'last_seen_at' => $now,
            ],
        );

        $visitor->forceFill(['last_seen_at' => $now])->save();
        $session->forceFill([
            'tracking_visitor_id' => $visitor->getKey(),
            'storage_mode' => $storageMode,
            'last_seen_at' => $now,
        ])->save();

        $request->session()->put('tracking.identifiers', [
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
            'storage_mode' => $storageMode,
            'last_seen_at' => $now->toIso8601String(),
        ]);

        if ($storageMode === 'cookie') {
            $minutes = max(1, (int) config('tracking.cookie_days', 30)) * 1440;
            Cookie::queue($this->identifierCookie((string) config('tracking.cookies.visitor'), $visitorId, $minutes, $request));
            Cookie::queue($this->identifierCookie((string) config('tracking.cookies.session'), $sessionId, $minutes, $request));
        }

        return [
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
            'storage_mode' => $storageMode,
        ];
    }

    public function sanitizeIdentifier(?string $value): string
    {
        $value = trim((string) $value);

        return preg_match('/^[a-zA-Z0-9_-]{8,96}$/', $value) ? $value : '';
    }

    private function generateIdentifier(string $prefix): string
    {
        return $prefix.'_'.Str::random(32);
    }

    private function identifierCookie(string $name, string $value, int $minutes, Request $request): \Symfony\Component\HttpFoundation\Cookie
    {
        return Cookie::make(
            name: $name,
            value: $value,
            minutes: $minutes,
            path: '/',
            domain: null,
            secure: $request->isSecure(),
            httpOnly: false,
            raw: false,
            sameSite: 'lax',
        );
    }
}

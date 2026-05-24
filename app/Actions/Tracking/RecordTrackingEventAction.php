<?php

namespace App\Actions\Tracking;

use App\Models\TrackingContactAttempt;
use App\Models\TrackingPageVisit;
use App\Models\TrackingSession;
use App\Models\TrackingVisitor;
use App\Services\Tracking\TrackingConsentService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecordTrackingEventAction
{
    public function __construct(
        private readonly ResolveTrackingIdentifiersAction $resolveIdentifiers,
        private readonly TrackingConsentService $consentService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $consentState
     * @return array{status:int,body:array<string, mixed>}
     */
    public function handle(Request $request, array $payload, array $consentState): array
    {
        $eventType = $this->sanitizeEventType((string) ($payload['event_type'] ?? 'pageview'));

        if (! in_array($eventType, ['pageview', 'heartbeat', 'page_end', 'contact_attempt', 'form_submit'], true)) {
            return ['status' => 422, 'body' => ['ok' => false, 'error' => 'Unsupported event type.']];
        }

        if (! $this->consentService->hasConsent($consentState, 'analytics')) {
            return [
                'status' => 202,
                'body' => [
                    'ok' => true,
                    'skipped' => true,
                    'reason' => 'analytics_consent_required',
                    'event_type' => $eventType,
                ],
            ];
        }

        $identifiers = $this->resolveIdentifiers->handle(
            $request,
            $consentState,
            isset($payload['visitor_id']) ? (string) $payload['visitor_id'] : null,
            isset($payload['session_id']) ? (string) $payload['session_id'] : null,
        );

        $timestamp = $this->normalizeTimestamp($payload['at'] ?? null);
        $shared = $this->sharedAttributes($payload);
        $pageVisitIdentifier = $this->sanitizeIdentifier((string) ($payload['page_visit_id'] ?? ''));

        $result = DB::transaction(function () use ($identifiers, $timestamp, $shared, $eventType, $pageVisitIdentifier, $payload): array {
            $visitor = TrackingVisitor::query()->where('identifier', $identifiers['visitor_id'])->lockForUpdate()->firstOrFail();
            $session = TrackingSession::query()->where('identifier', $identifiers['session_id'])->lockForUpdate()->firstOrFail();

            $visitor->forceFill([
                'last_seen_at' => $timestamp,
                'first_referrer' => $visitor->first_referrer ?: $shared['referrer'],
                'first_device' => $visitor->first_device ?: $shared['device'],
                'first_utm_source' => $visitor->first_utm_source ?: $shared['utm_source'],
                'first_utm_medium' => $visitor->first_utm_medium ?: $shared['utm_medium'],
                'first_utm_campaign' => $visitor->first_utm_campaign ?: $shared['utm_campaign'],
                'first_utm_content' => $visitor->first_utm_content ?: $shared['utm_content'],
                'first_utm_term' => $visitor->first_utm_term ?: $shared['utm_term'],
                'first_gclid' => $visitor->first_gclid ?: $shared['gclid'],
                'first_fbclid' => $visitor->first_fbclid ?: $shared['fbclid'],
            ])->save();

            $session->forceFill([
                'tracking_visitor_id' => $visitor->getKey(),
                'last_seen_at' => $timestamp,
            ])->save();

            return match ($eventType) {
                'pageview' => $this->recordPageview($visitor, $session, $pageVisitIdentifier, $timestamp, $shared),
                'heartbeat' => $this->recordHeartbeat($visitor, $session, $pageVisitIdentifier, $timestamp, $shared),
                'page_end' => $this->recordPageEnd($visitor, $session, $pageVisitIdentifier, $timestamp, $shared),
                default => $this->recordContactAttempt($visitor, $session, $pageVisitIdentifier, $timestamp, $shared, $payload, $eventType),
            };
        });

        return [
            'status' => 200,
            'body' => array_merge($result, $identifiers),
        ];
    }

    /**
     * @param  array<string, string|bool|null>  $shared
     * @return array<string, mixed>
     */
    private function recordPageview(TrackingVisitor $visitor, TrackingSession $session, string $identifier, Carbon $timestamp, array $shared): array
    {
        $identifier = $identifier ?: $this->generateIdentifier('pv');
        $pageVisit = TrackingPageVisit::query()->firstOrCreate(
            ['identifier' => $identifier],
            array_merge($shared, [
                'tracking_visitor_id' => $visitor->getKey(),
                'tracking_session_id' => $session->getKey(),
                'identifier' => $identifier,
                'started_at' => $timestamp,
                'last_seen_at' => $timestamp,
            ]),
        );

        if ($pageVisit->wasRecentlyCreated) {
            $visitor->increment('pageview_count');
            $session->increment('pageview_count');
        }

        return ['ok' => true, 'event_type' => 'pageview', 'page_visit_id' => $pageVisit->identifier];
    }

    /**
     * @param  array<string, string|bool|null>  $shared
     * @return array<string, mixed>
     */
    private function recordHeartbeat(TrackingVisitor $visitor, TrackingSession $session, string $identifier, Carbon $timestamp, array $shared): array
    {
        if ($identifier === '') {
            return ['ok' => true, 'event_type' => 'heartbeat', 'page_visit_id' => null];
        }

        $pageVisit = $this->findOrCreatePageVisit($visitor, $session, $identifier, $timestamp, $shared);
        $nextHeartbeatCount = (int) $pageVisit->heartbeat_count + 1;
        $pageVisit->forceFill([
            'last_seen_at' => $timestamp,
            'heartbeat_count' => $nextHeartbeatCount,
            'estimated_seconds' => $nextHeartbeatCount * max(5, (int) config('tracking.heartbeat_seconds', 30)),
        ])->save();

        return ['ok' => true, 'event_type' => 'heartbeat', 'page_visit_id' => $pageVisit->identifier];
    }

    /**
     * @param  array<string, string|bool|null>  $shared
     * @return array<string, mixed>
     */
    private function recordPageEnd(TrackingVisitor $visitor, TrackingSession $session, string $identifier, Carbon $timestamp, array $shared): array
    {
        if ($identifier === '') {
            return ['ok' => true, 'event_type' => 'page_end', 'page_visit_id' => null];
        }

        $pageVisit = $this->findOrCreatePageVisit($visitor, $session, $identifier, $timestamp, $shared);
        $pageVisit->forceFill([
            'last_seen_at' => $timestamp,
            'ended_at' => $timestamp,
        ])->save();

        return ['ok' => true, 'event_type' => 'page_end', 'page_visit_id' => $pageVisit->identifier];
    }

    /**
     * @param  array<string, string|bool|null>  $shared
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function recordContactAttempt(
        TrackingVisitor $visitor,
        TrackingSession $session,
        string $pageVisitIdentifier,
        Carbon $timestamp,
        array $shared,
        array $payload,
        string $eventType,
    ): array {
        $pageVisit = $pageVisitIdentifier !== ''
            ? TrackingPageVisit::query()->where('identifier', $pageVisitIdentifier)->first()
            : null;

        TrackingContactAttempt::query()->create(array_merge($shared, [
            'tracking_visitor_id' => $visitor->getKey(),
            'tracking_session_id' => $session->getKey(),
            'tracking_page_visit_id' => $pageVisit?->getKey(),
            'event_type' => $eventType,
            'contact_type' => $this->sanitizeString((string) ($payload['contact_type'] ?? '')),
            'href' => $this->sanitizeUrl((string) ($payload['href'] ?? '')),
            'link_text' => Str::limit($this->sanitizeString((string) ($payload['link_text'] ?? '')), 500, ''),
            'form_action' => $this->sanitizeUrl((string) ($payload['form_action'] ?? '')),
            'form_id' => $this->sanitizeString((string) ($payload['form_id'] ?? '')),
            'form_name' => $this->sanitizeString((string) ($payload['form_name'] ?? '')),
            'form_method' => Str::limit($this->sanitizeString((string) ($payload['form_method'] ?? '')), 16, ''),
            'occurred_at' => $timestamp,
        ]));

        $visitor->increment('contact_attempt_count');
        $session->increment('contact_attempt_count');

        return ['ok' => true, 'event_type' => $eventType];
    }

    /**
     * @param  array<string, string|bool|null>  $shared
     */
    private function findOrCreatePageVisit(TrackingVisitor $visitor, TrackingSession $session, string $identifier, Carbon $timestamp, array $shared): TrackingPageVisit
    {
        return TrackingPageVisit::query()->firstOrCreate(
            ['identifier' => $identifier],
            array_merge($shared, [
                'tracking_visitor_id' => $visitor->getKey(),
                'tracking_session_id' => $session->getKey(),
                'identifier' => $identifier,
                'started_at' => $timestamp,
                'last_seen_at' => $timestamp,
            ]),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, string|bool|null>
     */
    private function sharedAttributes(array $payload): array
    {
        $source = is_array($payload['source'] ?? null) ? $payload['source'] : [];

        return [
            'slug' => $this->sanitizeString((string) ($payload['slug'] ?? '')),
            'path' => $this->sanitizePath((string) ($payload['path'] ?? '')),
            'url' => $this->sanitizeUrl((string) ($payload['url'] ?? '')),
            'title' => $this->sanitizeString((string) ($payload['title'] ?? '')),
            'referrer' => $this->sanitizeUrl((string) ($payload['referrer'] ?? '')),
            'device' => Str::limit($this->sanitizeString((string) ($payload['device'] ?? '')), 32, ''),
            'landing' => (bool) ($payload['landing'] ?? false),
            'utm_source' => $this->sanitizeString((string) ($source['utm_source'] ?? '')),
            'utm_medium' => $this->sanitizeString((string) ($source['utm_medium'] ?? '')),
            'utm_campaign' => $this->sanitizeString((string) ($source['utm_campaign'] ?? '')),
            'utm_content' => $this->sanitizeString((string) ($source['utm_content'] ?? '')),
            'utm_term' => $this->sanitizeString((string) ($source['utm_term'] ?? '')),
            'gclid' => $this->sanitizeString((string) ($source['gclid'] ?? '')),
            'fbclid' => $this->sanitizeString((string) ($source['fbclid'] ?? '')),
        ];
    }

    private function sanitizeEventType(string $eventType): string
    {
        return Str::of($eventType)->lower()->replaceMatches('/[^a-z0-9_]+/', '_')->trim('_')->limit(32, '')->value();
    }

    private function sanitizeIdentifier(string $value): string
    {
        return preg_match('/^[a-zA-Z0-9_-]{8,96}$/', $value) ? $value : '';
    }

    private function sanitizeString(string $value): string
    {
        return Str::limit(trim(strip_tags($value)), 255, '');
    }

    private function sanitizePath(string $value): string
    {
        return Str::limit(parse_url($value, PHP_URL_PATH) ?: $value, 255, '');
    }

    private function sanitizeUrl(string $value): string
    {
        return Str::limit(trim(strip_tags($value)), 2048, '');
    }

    private function normalizeTimestamp(mixed $value): Carbon
    {
        try {
            return filled($value) ? Carbon::parse((string) $value) : now();
        } catch (\Throwable) {
            return now();
        }
    }

    private function generateIdentifier(string $prefix): string
    {
        return $prefix.'_'.Str::random(32);
    }
}

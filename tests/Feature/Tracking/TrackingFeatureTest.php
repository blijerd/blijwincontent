<?php

namespace Tests\Feature\Tracking;

use App\Models\TrackingConsentDecision;
use App\Models\TrackingContactAttempt;
use App\Models\TrackingPageVisit;
use App\Models\TrackingSession;
use App\Models\TrackingVisitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_consent_endpoint_returns_default_state_and_server_session_identifiers(): void
    {
        $response = $this->getJson(route('tracking.consent'));

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('consent.hasDecision', false)
            ->assertJsonPath('consent.categories.necessary', true)
            ->assertJsonPath('consent.categories.analytics', false)
            ->assertJsonPath('consent.categories.marketing', false)
            ->assertJsonPath('identifiers.storage_mode', 'server_session');

        $this->assertSame(1, TrackingVisitor::query()->count());
        $this->assertSame(1, TrackingSession::query()->count());
    }

    public function test_consent_post_persists_decision_and_switches_identifier_storage_to_cookie(): void
    {
        $getResponse = $this->getJson(route('tracking.consent'));
        $visitorIdentifier = (string) $getResponse->json('identifiers.visitor_id');

        $response = $this->postJson(route('tracking.consent'), [
            'categories' => [
                'analytics' => true,
                'marketing' => false,
            ],
            'source' => 'initial_preferences',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('consent.hasDecision', true)
            ->assertJsonPath('consent.categories.analytics', true)
            ->assertJsonPath('identifiers.storage_mode', 'cookie')
            ->assertCookie((string) config('tracking.cookies.consent'))
            ->assertCookie((string) config('tracking.cookies.visitor'));

        $this->assertDatabaseHas('tracking_consent_decisions', [
            'client_identifier' => $visitorIdentifier,
            'analytics_granted' => true,
            'marketing_granted' => false,
            'source' => 'initial_preferences',
            'storage_mode' => 'cookie',
        ]);

        $this->assertSame(1, TrackingConsentDecision::query()->count());
    }

    public function test_collect_route_stores_pageviews_heartbeats_page_end_and_contact_attempts_after_analytics_consent(): void
    {
        $consentResponse = $this->postJson(route('tracking.consent'), [
            'categories' => [
                'analytics' => true,
                'marketing' => false,
            ],
            'source' => 'initial_preferences',
        ]);

        $visitorId = (string) $consentResponse->json('identifiers.visitor_id');
        $sessionId = (string) $consentResponse->json('identifiers.session_id');

        $this->postJson(route('tracking.collect'), [
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
            'page_visit_id' => 'pv_test_content',
            'event_type' => 'pageview',
            'slug' => 'home',
            'path' => '/',
            'url' => 'https://content.test/',
            'title' => 'Home',
            'referrer' => 'https://example.test/',
            'device' => 'desktop',
            'landing' => true,
            'source' => [
                'utm_source' => 'nieuwsbrief',
                'utm_medium' => 'email',
                'utm_campaign' => 'voorjaar',
            ],
        ])->assertOk();

        $this->postJson(route('tracking.collect'), [
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
            'page_visit_id' => 'pv_test_content',
            'event_type' => 'heartbeat',
        ])->assertOk();

        $this->postJson(route('tracking.collect'), [
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
            'page_visit_id' => 'pv_test_content',
            'event_type' => 'page_end',
        ])->assertOk();

        $this->postJson(route('tracking.collect'), [
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
            'page_visit_id' => 'pv_test_content',
            'event_type' => 'contact_attempt',
            'contact_type' => 'email',
            'href' => 'mailto:info@content.test',
            'link_text' => 'Mail ons',
        ])->assertOk();

        $visitor = TrackingVisitor::query()->firstOrFail();
        $session = TrackingSession::query()->firstOrFail();
        $pageVisit = TrackingPageVisit::query()->firstOrFail();
        $contactAttempt = TrackingContactAttempt::query()->firstOrFail();

        $this->assertSame($visitorId, $visitor->identifier);
        $this->assertSame(1, $visitor->pageview_count);
        $this->assertSame(1, $visitor->contact_attempt_count);
        $this->assertSame($sessionId, $session->identifier);
        $this->assertSame(1, $session->pageview_count);
        $this->assertSame(1, $session->contact_attempt_count);
        $this->assertSame('pv_test_content', $pageVisit->identifier);
        $this->assertTrue($pageVisit->landing);
        $this->assertSame(1, $pageVisit->heartbeat_count);
        $this->assertNotNull($pageVisit->ended_at);
        $this->assertSame('nieuwsbrief', $pageVisit->utm_source);
        $this->assertSame('email', $contactAttempt->contact_type);
        $this->assertSame('mailto:info@content.test', $contactAttempt->href);
    }

    public function test_collect_route_skips_analytics_events_without_analytics_consent(): void
    {
        $consentResponse = $this->getJson(route('tracking.consent'));

        $this->postJson(route('tracking.collect'), [
            'visitor_id' => (string) $consentResponse->json('identifiers.visitor_id'),
            'session_id' => (string) $consentResponse->json('identifiers.session_id'),
            'event_type' => 'pageview',
        ])
            ->assertAccepted()
            ->assertJsonPath('skipped', true)
            ->assertJsonPath('reason', 'analytics_consent_required');

        $this->assertSame(0, TrackingPageVisit::query()->count());
    }
}

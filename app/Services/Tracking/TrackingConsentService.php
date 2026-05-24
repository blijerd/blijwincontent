<?php

namespace App\Services\Tracking;

use App\Models\TrackingConsentDecision;
use App\Models\TrackingVisitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class TrackingConsentService
{
    private string $sessionKey = 'tracking.consent';

    /**
     * @return array<string, mixed>
     */
    public function frontendState(Request $request): array
    {
        $state = $this->getConsentState($request);

        return [
            'hasDecision' => (bool) ($state['has_decision'] ?? false),
            'categories' => $state['categories'] ?? $this->defaultCategoryValues(),
            'updatedAt' => $state['updated_at'] ?? null,
            'source' => $state['source'] ?? null,
            'identifierCookieCategory' => $this->identifierCookieCategory(),
            'categoryDefinitions' => $this->categoryDefinitions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getConsentState(Request $request): array
    {
        $state = $request->session()->get($this->sessionKey);
        $state = is_array($state) ? $this->normalizeState($state) : null;

        if ($state === null) {
            $state = $this->decodePayload($request->cookie($this->consentCookieName()));
        }

        if ($state === null) {
            return $this->emptyState();
        }

        $request->session()->put($this->sessionKey, $state);

        return $state;
    }

    /**
     * @param  array<string, mixed>  $requestedCategories
     * @return array<string, mixed>
     */
    public function saveConsent(Request $request, array $requestedCategories, string $source, ?string $visitorIdentifier = null): array
    {
        $state = [
            'version' => 1,
            'has_decision' => true,
            'categories' => $this->normalizeCategories($requestedCategories),
            'updated_at' => now()->toIso8601String(),
            'source' => $this->sanitizeSource($source),
        ];

        Cookie::queue($this->makeCookie(
            $this->consentCookieName(),
            $this->encodePayload($state),
            max($this->consentCookieDays(), 1) * 1440,
            httpOnly: false,
            request: $request,
        ));

        $request->session()->put($this->sessionKey, $state);

        $visitor = filled($visitorIdentifier)
            ? TrackingVisitor::query()->where('identifier', $visitorIdentifier)->first()
            : null;

        TrackingConsentDecision::query()->create([
            'tracking_visitor_id' => $visitor?->getKey(),
            'client_identifier' => $visitorIdentifier,
            'source' => $state['source'],
            'necessary_granted' => true,
            'analytics_granted' => (bool) ($state['categories']['analytics'] ?? false),
            'marketing_granted' => (bool) ($state['categories']['marketing'] ?? false),
            'storage_mode' => $this->shouldPersistIdentifiersInCookies($state) ? 'cookie' : 'server_session',
            'request_ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 65535, ''),
            'decided_at' => now(),
        ]);

        return $state;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public function hasConsent(array $state, string $category): bool
    {
        return (bool) ($state['categories'][$category] ?? false);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public function shouldPersistIdentifiersInCookies(array $state): bool
    {
        return $this->hasConsent($state, $this->identifierCookieCategory());
    }

    /**
     * @return array<string, array{label:string,description:string,required:bool}>
     */
    public function categoryDefinitions(): array
    {
        /** @var array<string, array{label:string,description:string,required:bool}> $definitions */
        $definitions = config('tracking.consent.categories', []);
        $definitions['necessary']['required'] = true;

        return $definitions;
    }

    public function identifierCookieCategory(): string
    {
        $category = Str::of((string) config('tracking.consent.identifier_cookie_category', 'analytics'))
            ->trim()
            ->lower()
            ->value();

        return array_key_exists($category, $this->categoryDefinitions()) ? $category : 'analytics';
    }

    public function consentCookieName(): string
    {
        return (string) config('tracking.cookies.consent', 'tw_consent');
    }

    public function consentCookieDays(): int
    {
        return (int) config('tracking.consent.cookie_days', 180);
    }

    /**
     * @return array<string, bool>
     */
    public function defaultCategoryValues(): array
    {
        $values = [];

        foreach ($this->categoryDefinitions() as $category => $definition) {
            $values[$category] = (bool) ($definition['required'] ?? false);
        }

        return $values;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyState(): array
    {
        return [
            'version' => 1,
            'has_decision' => false,
            'categories' => $this->defaultCategoryValues(),
            'updated_at' => null,
            'source' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $requestedCategories
     * @return array<string, bool>
     */
    private function normalizeCategories(array $requestedCategories): array
    {
        $normalized = [];

        foreach ($this->categoryDefinitions() as $category => $definition) {
            $normalized[$category] = (bool) ($definition['required'] ?? false);

            if (($definition['required'] ?? false) === false && array_key_exists($category, $requestedCategories)) {
                $normalized[$category] = filter_var($requestedCategories[$category], FILTER_VALIDATE_BOOL);
            }
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>|null
     */
    private function normalizeState(array $state): ?array
    {
        if (! isset($state['categories']) || ! is_array($state['categories'])) {
            return null;
        }

        return [
            'version' => (int) ($state['version'] ?? 1),
            'has_decision' => (bool) ($state['has_decision'] ?? true),
            'categories' => $this->normalizeCategories($state['categories']),
            'updated_at' => isset($state['updated_at']) ? (string) $state['updated_at'] : null,
            'source' => isset($state['source']) ? $this->sanitizeSource((string) $state['source']) : null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodePayload(?string $value): ?array
    {
        if (! filled($value)) {
            return null;
        }

        $decoded = json_decode(base64_decode((string) $value, true) ?: '', true);

        return is_array($decoded) ? $this->normalizeState($decoded) : null;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function encodePayload(array $state): string
    {
        return base64_encode((string) json_encode($state, JSON_UNESCAPED_SLASHES));
    }

    private function sanitizeSource(string $source): string
    {
        return Str::of($source)->lower()->replaceMatches('/[^a-z0-9_:-]+/', '_')->trim('_')->limit(64, '')->value() ?: 'update_preferences';
    }

    private function makeCookie(string $name, string $value, int $minutes, bool $httpOnly, Request $request): \Symfony\Component\HttpFoundation\Cookie
    {
        return Cookie::make(
            name: $name,
            value: $value,
            minutes: $minutes,
            path: '/',
            domain: null,
            secure: $request->isSecure(),
            httpOnly: $httpOnly,
            raw: false,
            sameSite: 'lax',
        );
    }
}

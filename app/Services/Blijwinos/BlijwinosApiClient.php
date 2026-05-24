<?php

namespace App\Services\Blijwinos;

use App\Exceptions\BlijwinosApiException;
use App\Models\BlijwinosApiLog;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JsonException;

class BlijwinosApiClient
{
    /** @return array<string, mixed> */
    public function catalog(?string $type = null, ?string $package = null, bool $fresh = false): array
    {
        $query = array_filter([
            'type' => $type,
            'package' => $package,
        ], fn (?string $value): bool => filled($value));

        return $this->readJson(
            endpoint: $this->endpoint('catalog'),
            query: $query,
            cacheKey: 'blijwinos:catalog:'.md5(json_encode($query, JSON_THROW_ON_ERROR)),
            fresh: $fresh,
        );
    }

    /** @return array<string, mixed> */
    public function priceLists(?string $type = null, ?string $package = null, ?string $scope = null, bool $fresh = false): array
    {
        $query = array_filter([
            'type' => $type,
            'package' => $package,
            'scope' => $scope,
        ], fn (?string $value): bool => filled($value));

        return $this->readJson(
            endpoint: $this->endpoint('price_lists'),
            query: $query,
            cacheKey: 'blijwinos:price-lists:'.md5(json_encode($query, JSON_THROW_ON_ERROR)),
            fresh: $fresh,
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function submitBookingRequest(array $payload): array
    {
        return $this->writeJson($this->endpoint('booking_requests'), ['form' => $payload]);
    }

    /**
     * @param array<string, string> $query
     * @return array<string, mixed>
     */
    private function readJson(string $endpoint, array $query, string $cacheKey, bool $fresh): array
    {
        if (! $fresh && $this->readCacheSeconds() > 0) {
            return Cache::remember(
                $cacheKey,
                $this->readCacheSeconds(),
                fn (): array => $this->sendJson('GET', $endpoint, query: $query, direction: 'read'),
            );
        }

        return $this->sendJson('GET', $endpoint, query: $query, direction: 'read');
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function writeJson(string $endpoint, array $payload): array
    {
        return $this->sendJson('POST', $endpoint, payload: $payload, direction: 'write');
    }

    /**
     * @param array<string, string> $query
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function sendJson(
        string $method,
        string $endpoint,
        array $query = [],
        array $payload = [],
        string $direction = 'read',
    ): array {
        $this->ensureEnabled();

        $requestId = (string) Str::uuid();
        $startedAt = microtime(true);
        $statusCode = null;

        try {
            $request = $this->request();

            if ($method === 'POST') {
                $request = $request->withHeaders($this->signatureHeaders($endpoint, $payload, $requestId));
                $response = $request->post($endpoint, $payload);
            } else {
                $response = $request->get($endpoint, $query);
            }

            $statusCode = $response->status();
            $this->log($direction, $method, $endpoint, $statusCode, $response->successful(), $startedAt, $requestId);

            if (! $response->successful()) {
                throw new BlijwinosApiException(
                    sprintf('Blijwin OS API returned HTTP %d for %s %s.', $statusCode, $method, $endpoint),
                    $statusCode,
                    $endpoint,
                );
            }

            return $this->json($response, $endpoint);
        } catch (BlijwinosApiException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->log(
                $direction,
                $method,
                $endpoint,
                $statusCode,
                false,
                $startedAt,
                $requestId,
                $exception,
            );

            throw new BlijwinosApiException(
                'Blijwin OS API request failed: '.$exception->getMessage(),
                $statusCode,
                $endpoint,
            );
        }
    }

    private function request(): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeout())
            ->retry($this->retries(), $this->retryDelay(), throw: false)
            ->withHeaders([
                'X-Blijwin-Origin' => config('settings.blijwinos.origin', 'blijwincontent'),
            ]);

        $catalogKey = $this->catalogKey();

        if ($catalogKey !== '') {
            $request = $request->withHeaders(['X-Blijwin-Catalog-Key' => $catalogKey]);
        }

        return $request;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, string>
     *
     * @throws JsonException
     */
    private function signatureHeaders(string $endpoint, array $payload, string $requestId): array
    {
        $secret = $this->writeSecret();

        if ($secret === '') {
            throw new BlijwinosApiException('Blijwin OS write secret is not configured.', endpoint: $endpoint);
        }

        $timestamp = (string) now()->timestamp;
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $canonical = "POST\n{$endpoint}\n{$timestamp}\n{$requestId}\n{$body}";

        return [
            'X-Blijwin-Timestamp' => $timestamp,
            'X-Blijwin-Request-Id' => $requestId,
            'X-Blijwin-Signature' => 'sha256='.hash_hmac('sha256', $canonical, $secret),
        ];
    }

    /** @return array<string, mixed> */
    private function json(Response $response, string $endpoint): array
    {
        $json = $response->json();

        if (! is_array($json)) {
            throw new BlijwinosApiException('Blijwin OS API did not return a JSON object.', $response->status(), $endpoint);
        }

        return $json;
    }

    private function log(
        string $direction,
        string $method,
        string $endpoint,
        ?int $statusCode,
        bool $successful,
        float $startedAt,
        string $requestId,
        ?\Throwable $exception = null,
    ): void {
        BlijwinosApiLog::query()->create([
            'direction' => $direction,
            'method' => $method,
            'endpoint' => $endpoint,
            'status_code' => $statusCode,
            'successful' => $successful,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'request_id' => $requestId,
            'error_class' => $exception ? $exception::class : null,
            'error_message' => $exception?->getMessage(),
            'metadata' => [
                'base_url' => $this->baseUrl(),
            ],
        ]);
    }

    private function ensureEnabled(): void
    {
        if (! (bool) config('settings.blijwinos.enabled', false)) {
            throw new BlijwinosApiException('Blijwin OS integration is disabled.');
        }
    }

    private function endpoint(string $key): string
    {
        return (string) config("settings.blijwinos.endpoints.{$key}");
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('settings.blijwinos.base_url'), '/');
    }

    private function catalogKey(): string
    {
        return trim((string) config('settings.blijwinos.catalog_key', ''));
    }

    private function writeSecret(): string
    {
        return trim((string) config('settings.blijwinos.write_hmac_secret', ''));
    }

    private function timeout(): int
    {
        return max(2, (int) config('settings.blijwinos.timeout_seconds', 10));
    }

    private function retries(): int
    {
        return max(0, (int) config('settings.blijwinos.retries', 2));
    }

    private function retryDelay(): int
    {
        return max(0, (int) config('settings.blijwinos.retry_delay_ms', 250));
    }

    private function readCacheSeconds(): int
    {
        return max(0, (int) config('settings.blijwinos.read_cache_seconds', 300));
    }
}

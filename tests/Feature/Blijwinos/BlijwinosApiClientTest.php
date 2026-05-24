<?php

namespace Tests\Feature\Blijwinos;

use App\Exceptions\BlijwinosApiException;
use App\Models\BlijwinosApiLog;
use App\Services\Blijwinos\BlijwinosApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BlijwinosApiClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reads_catalog_data_with_cache_and_logging(): void
    {
        Cache::flush();
        config()->set('settings.blijwinos.enabled', true);
        config()->set('settings.blijwinos.base_url', 'https://blijwinos.test');
        config()->set('settings.blijwinos.read_cache_seconds', 600);

        Http::fake([
            'https://blijwinos.test/api/blijwinboekingen/catalogus?type=schuimparty' => Http::response([
                'data' => [
                    ['slug' => 'schuimparty', 'title' => 'Schuimparty'],
                ],
            ]),
        ]);

        $client = app(BlijwinosApiClient::class);

        $first = $client->catalog(type: 'schuimparty');
        $second = $client->catalog(type: 'schuimparty');

        $this->assertSame($first, $second);
        $this->assertSame('Schuimparty', $first['data'][0]['title']);
        Http::assertSentCount(1);
        $this->assertDatabaseHas('blijwinos_api_logs', [
            'direction' => 'read',
            'method' => 'GET',
            'endpoint' => '/api/blijwinboekingen/catalogus',
            'status_code' => 200,
            'successful' => true,
        ]);
    }

    public function test_it_writes_booking_requests_with_blijwinos_hmac_headers(): void
    {
        config()->set('settings.blijwinos.enabled', true);
        config()->set('settings.blijwinos.base_url', 'https://blijwinos.test');
        config()->set('settings.blijwinos.write_hmac_secret', 'test-write-secret');
        $this->travelTo(now()->setTimestamp(1_800_000_000));

        Http::fake([
            'https://blijwinos.test/api/blijwinboekingen/aanvragen' => Http::response([
                'message' => 'Aanvraag ontvangen.',
                'data' => ['public_id' => 'request-public-id'],
            ], 201),
        ]);

        $payload = [
            'first_name' => 'Edwin',
            'email' => 'edwin@example.com',
            'package_slug' => 'schuimparty',
        ];

        $response = app(BlijwinosApiClient::class)->submitBookingRequest($payload);

        $this->assertSame('request-public-id', $response['data']['public_id']);
        Http::assertSent(function (Request $request) use ($payload): bool {
            $requestId = $request->header('X-Blijwin-Request-Id')[0] ?? '';
            $body = json_encode(['form' => $payload], JSON_THROW_ON_ERROR);
            $signature = hash_hmac(
                'sha256',
                "POST\n/api/blijwinboekingen/aanvragen\n1800000000\n{$requestId}\n{$body}",
                'test-write-secret',
            );

            return $request->method() === 'POST'
                && $request->url() === 'https://blijwinos.test/api/blijwinboekingen/aanvragen'
                && $request->header('X-Blijwin-Timestamp')[0] === '1800000000'
                && $request->header('X-Blijwin-Origin')[0] === 'blijwincontent'
                && $request->header('X-Blijwin-Signature')[0] === 'sha256='.$signature;
        });
        $this->assertDatabaseHas('blijwinos_api_logs', [
            'direction' => 'write',
            'method' => 'POST',
            'status_code' => 201,
            'successful' => true,
        ]);
    }

    public function test_it_refuses_writes_without_configured_hmac_secret(): void
    {
        config()->set('settings.blijwinos.enabled', true);
        config()->set('settings.blijwinos.base_url', 'https://blijwinos.test');
        config()->set('settings.blijwinos.write_hmac_secret', null);

        $this->expectException(BlijwinosApiException::class);
        $this->expectExceptionMessage('write secret');

        app(BlijwinosApiClient::class)->submitBookingRequest(['email' => 'edwin@example.com']);
    }

    public function test_it_logs_failed_responses(): void
    {
        config()->set('settings.blijwinos.enabled', true);
        config()->set('settings.blijwinos.base_url', 'https://blijwinos.test');
        config()->set('settings.blijwinos.read_cache_seconds', 0);

        Http::fake([
            'https://blijwinos.test/api/blijwinboekingen/catalogus' => Http::response([
                'message' => 'Service unavailable',
            ], 503),
        ]);

        try {
            app(BlijwinosApiClient::class)->catalog();
        } catch (BlijwinosApiException) {
            // Expected.
        }

        $this->assertDatabaseHas('blijwinos_api_logs', [
            'direction' => 'read',
            'method' => 'GET',
            'status_code' => 503,
            'successful' => false,
        ]);
        $this->assertSame(1, BlijwinosApiLog::query()->count());
    }
}

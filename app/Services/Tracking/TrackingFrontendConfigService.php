<?php

namespace App\Services\Tracking;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class TrackingFrontendConfigService
{
    public function __construct(private readonly TrackingConsentService $consentService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        return [
            'collectEndpoint' => Route::has('tracking.collect') ? route('tracking.collect', [], false) : (string) config('tracking.routes.collect'),
            'consentEndpoint' => Route::has('tracking.consent') ? route('tracking.consent', [], false) : (string) config('tracking.routes.consent'),
            'fieldName' => (string) config('tracking.field_name', 'data[blijwin_t_info]'),
            'heartbeatSeconds' => (int) config('tracking.heartbeat_seconds', 30),
            'consentDefaults' => $this->consentService->frontendState($request),
            'pageSlug' => $this->pageSlug($request),
        ];
    }

    private function pageSlug(Request $request): string
    {
        $path = trim($request->path(), '/');

        return $path === '' ? 'home' : str_replace('/', '-', $path);
    }
}

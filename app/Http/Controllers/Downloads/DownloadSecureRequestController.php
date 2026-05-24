<?php

namespace App\Http\Controllers\Downloads;

use App\Http\Controllers\Controller;
use App\Services\Downloads\DownloadSecureRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DownloadSecureRequestController extends Controller
{
    public function __invoke(Request $request, DownloadSecureRequestService $service): JsonResponse
    {
        return response()->json($service->request($request));
    }
}

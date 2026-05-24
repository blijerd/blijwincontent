<?php

namespace App\Http\Controllers\Downloads;

use App\Http\Controllers\Controller;
use App\Services\Downloads\DownloadDeliveryService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class DownloadSecureController extends Controller
{
    public function __invoke(string $token, DownloadDeliveryService $delivery): Response|BinaryFileResponse
    {
        return $delivery->secure($token);
    }
}

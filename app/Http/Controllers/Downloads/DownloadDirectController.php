<?php

namespace App\Http\Controllers\Downloads;

use App\Http\Controllers\Controller;
use App\Models\DownloadFormat;
use App\Services\Downloads\DownloadDeliveryService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class DownloadDirectController extends Controller
{
    public function __invoke(
        string $category,
        string $item,
        string $format,
        DownloadDeliveryService $delivery,
    ): Response|BinaryFileResponse {
        $downloadFormat = DownloadFormat::query()
            ->with('item.category')
            ->where('public_id', $format)
            ->whereHas('item', fn ($query) => $query
                ->where('public_id', $item)
                ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('public_id', $category)))
            ->firstOrFail();

        return $delivery->direct($downloadFormat);
    }
}

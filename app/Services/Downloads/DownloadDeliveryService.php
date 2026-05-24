<?php

namespace App\Services\Downloads;

use App\Models\DownloadFormat;
use App\Models\DownloadSecureToken;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class DownloadDeliveryService
{
    public function direct(DownloadFormat $format): Response|BinaryFileResponse
    {
        abort_if($format->is_secure, 404);

        return $this->deliver($format);
    }

    public function secure(string $token): Response|BinaryFileResponse
    {
        $record = DownloadSecureToken::query()
            ->with('format.item.category')
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $record->forceFill([
            'access_count' => $record->access_count + 1,
            'last_accessed_at' => now(),
        ])->save();

        return $this->deliver($record->format);
    }

    private function deliver(DownloadFormat $format): Response|BinaryFileResponse
    {
        if ($format->isExternal() || Str::startsWith($format->file_path, '/')) {
            return redirect($format->file_path);
        }

        $disk = Storage::disk('public');
        $path = ltrim($format->file_path, '/');

        abort_unless($disk->exists($path), 404);

        return response()->download(
            $disk->path($path),
            $this->downloadName($format),
        );
    }

    private function downloadName(DownloadFormat $format): string
    {
        $extension = pathinfo($format->file_path, PATHINFO_EXTENSION);
        $name = Str::slug($format->item?->title ?: 'download') ?: 'download';

        return $extension ? "{$name}.{$extension}" : $name;
    }
}

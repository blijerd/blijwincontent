<?php

namespace App\Filament\Resources\DownloadFormats\Pages;

use App\Filament\Resources\DownloadFormats\DownloadFormatResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDownloadFormat extends EditRecord
{
    protected static string $resource = DownloadFormatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

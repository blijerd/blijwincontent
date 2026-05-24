<?php

namespace App\Filament\Resources\DownloadItems\Pages;

use App\Filament\Resources\DownloadItems\DownloadItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDownloadItem extends EditRecord
{
    protected static string $resource = DownloadItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

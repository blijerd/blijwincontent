<?php

namespace App\Filament\Resources\DownloadItems\Pages;

use App\Filament\Resources\DownloadItems\DownloadItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDownloadItems extends ListRecords
{
    protected static string $resource = DownloadItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

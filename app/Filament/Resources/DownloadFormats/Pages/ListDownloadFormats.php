<?php

namespace App\Filament\Resources\DownloadFormats\Pages;

use App\Filament\Resources\DownloadFormats\DownloadFormatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDownloadFormats extends ListRecords
{
    protected static string $resource = DownloadFormatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

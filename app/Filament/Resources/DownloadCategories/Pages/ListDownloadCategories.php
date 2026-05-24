<?php

namespace App\Filament\Resources\DownloadCategories\Pages;

use App\Filament\Resources\DownloadCategories\DownloadCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDownloadCategories extends ListRecords
{
    protected static string $resource = DownloadCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\NavigationMenuItems\Pages;

use App\Filament\Resources\NavigationMenuItems\NavigationMenuItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNavigationMenuItems extends ListRecords
{
    protected static string $resource = NavigationMenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

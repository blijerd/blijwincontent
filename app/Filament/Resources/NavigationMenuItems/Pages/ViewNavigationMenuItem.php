<?php

namespace App\Filament\Resources\NavigationMenuItems\Pages;

use App\Filament\Resources\NavigationMenuItems\NavigationMenuItemResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewNavigationMenuItem extends ViewRecord
{
    protected static string $resource = NavigationMenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

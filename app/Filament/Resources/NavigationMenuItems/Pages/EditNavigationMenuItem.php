<?php

namespace App\Filament\Resources\NavigationMenuItems\Pages;

use App\Filament\Resources\NavigationMenuItems\NavigationMenuItemResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditNavigationMenuItem extends EditRecord
{
    protected static string $resource = NavigationMenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\NavigationMenuItems;

use App\Filament\Resources\NavigationMenuItems\Pages\CreateNavigationMenuItem;
use App\Filament\Resources\NavigationMenuItems\Pages\EditNavigationMenuItem;
use App\Filament\Resources\NavigationMenuItems\Pages\ListNavigationMenuItems;
use App\Filament\Resources\NavigationMenuItems\Pages\ViewNavigationMenuItem;
use App\Filament\Resources\NavigationMenuItems\Schemas\NavigationMenuItemForm;
use App\Filament\Resources\NavigationMenuItems\Tables\NavigationMenuItemsTable;
use App\Models\NavigationMenuItem;
use App\Support\Filament\AdminNavigation;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NavigationMenuItemResource extends Resource
{
    protected static ?string $model = NavigationMenuItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Menu-items';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigation::GROUP_STRUCTURE;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return NavigationMenuItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NavigationMenuItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNavigationMenuItems::route('/'),
            'create' => CreateNavigationMenuItem::route('/create'),
            'view' => ViewNavigationMenuItem::route('/{record}'),
            'edit' => EditNavigationMenuItem::route('/{record}/edit'),
        ];
    }
}

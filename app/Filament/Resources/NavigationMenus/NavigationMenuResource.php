<?php

namespace App\Filament\Resources\NavigationMenus;

use App\Filament\Resources\NavigationMenus\Pages\CreateNavigationMenu;
use App\Filament\Resources\NavigationMenus\Pages\EditNavigationMenu;
use App\Filament\Resources\NavigationMenus\Pages\ListNavigationMenus;
use App\Filament\Resources\NavigationMenus\Pages\ViewNavigationMenu;
use App\Filament\Resources\NavigationMenus\Schemas\NavigationMenuForm;
use App\Filament\Resources\NavigationMenus\Tables\NavigationMenusTable;
use App\Models\NavigationMenu;
use App\Support\Filament\AdminNavigation;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NavigationMenuResource extends Resource
{
    protected static ?string $model = NavigationMenu::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Menu’s';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigation::GROUP_STRUCTURE;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return NavigationMenuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NavigationMenusTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNavigationMenus::route('/'),
            'create' => CreateNavigationMenu::route('/create'),
            'view' => ViewNavigationMenu::route('/{record}'),
            'edit' => EditNavigationMenu::route('/{record}/edit'),
        ];
    }
}

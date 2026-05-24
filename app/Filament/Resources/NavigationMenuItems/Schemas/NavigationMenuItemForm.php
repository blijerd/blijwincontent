<?php

namespace App\Filament\Resources\NavigationMenuItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NavigationMenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('navigation_menu_id')
                    ->label('Menu')
                    ->relationship('menu', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('parent_id')
                    ->label('Subitem van')
                    ->relationship('parent', 'label')
                    ->searchable()
                    ->preload(),
                Select::make('page_id')
                    ->label('Pagina')
                    ->relationship('page', 'title')
                    ->searchable()
                    ->preload(),
                TextInput::make('label')->label('Label')->required()->maxLength(255),
                TextInput::make('url')
                    ->label('URL')
                    ->maxLength(255)
                    ->helperText('Gebruik dit voor externe of losse interne links. Laat leeg wanneer een pagina gekozen is.'),
                TextInput::make('sort_order')->label('Volgorde')->numeric()->default(0),
                Toggle::make('is_visible')->label('Zichtbaar')->default(true),
                Toggle::make('opens_in_new_tab')->label('Openen in nieuw venster')->default(false),
            ])
            ->columns(2);
    }
}

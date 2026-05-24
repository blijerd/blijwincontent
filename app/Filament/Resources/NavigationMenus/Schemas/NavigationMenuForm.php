<?php

namespace App\Filament\Resources\NavigationMenus\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NavigationMenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site_id')->relationship('site', 'name')->required(),
                TextInput::make('handle')
                    ->label('Sleutel')
                    ->required()
                    ->maxLength(64)
                    ->helperText('Gebruik main voor het hoofdmenu en audience voor Voor boekers/Voor fans.'),
                TextInput::make('title')->label('Titel')->required()->maxLength(255),
                TextInput::make('locale')->label('Taal')->required()->default('nl')->maxLength(12),
                Toggle::make('is_active')->label('Actief')->default(true),
            ])
            ->columns(2);
    }
}

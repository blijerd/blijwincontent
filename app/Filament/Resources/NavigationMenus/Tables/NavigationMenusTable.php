<?php

namespace App\Filament\Resources\NavigationMenus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NavigationMenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')->label('Site')->sortable(),
                TextColumn::make('title')->label('Titel')->searchable()->sortable(),
                TextColumn::make('handle')->label('Sleutel')->badge()->sortable(),
                TextColumn::make('locale')->label('Taal')->sortable(),
                IconColumn::make('is_active')->label('Actief')->boolean(),
                TextColumn::make('items_count')->label('Items')->counts('items'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

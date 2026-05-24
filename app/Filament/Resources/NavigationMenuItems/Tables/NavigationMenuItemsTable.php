<?php

namespace App\Filament\Resources\NavigationMenuItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NavigationMenuItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('menu.title')->label('Menu')->sortable(),
                TextColumn::make('parent.label')->label('Subitem van')->placeholder('-'),
                TextColumn::make('label')->label('Label')->searchable()->sortable(),
                TextColumn::make('page.full_path')->label('Pagina')->placeholder('-')->searchable(),
                TextColumn::make('url')->label('URL')->placeholder('-')->searchable(),
                TextColumn::make('sort_order')->label('Volgorde')->sortable(),
                IconColumn::make('is_visible')->label('Zichtbaar')->boolean(),
            ])
            ->defaultSort('sort_order')
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

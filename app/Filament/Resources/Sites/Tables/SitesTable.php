<?php

namespace App\Filament\Resources\Sites\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('domain')->searchable(),
                TextColumn::make('default_locale')->sortable(),
                TextColumn::make('search_indexing_mode')
                    ->label('Indexering')
                    ->formatStateUsing(fn ($state): string => $state instanceof \App\Enums\SearchIndexingMode
                        ? $state->label()
                        : \App\Enums\SearchIndexingMode::from($state)->label())
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_active')->boolean()->sortable(),
            ])
            ->filters([
                //
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

<?php

namespace App\Filament\Resources\DownloadItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DownloadItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.title')->label('Categorie')->searchable()->sortable(),
                TextColumn::make('title')->searchable()->wrap(),
                TextColumn::make('formats_count')->counts('formats')->label('Formats')->sortable(),
                IconColumn::make('is_active')->label('Actief')->boolean()->sortable(),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

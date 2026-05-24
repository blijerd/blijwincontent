<?php

namespace App\Filament\Resources\DownloadFormats\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DownloadFormatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item.category.title')->label('Categorie')->sortable(),
                TextColumn::make('item.title')->label('Download')->searchable()->sortable()->wrap(),
                TextColumn::make('label')->searchable()->sortable(),
                TextColumn::make('file_path')->searchable()->wrap(),
                IconColumn::make('is_secure')->label('Beveiligd')->boolean()->sortable(),
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

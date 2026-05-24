<?php

namespace App\Filament\Resources\Pages\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PageParentSelectTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')
                    ->label('Site')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_path')
                    ->label('Pad')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('locale')
                    ->label('Taal')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('locale')
                    ->label('Taal'),
                SelectFilter::make('status')
                    ->label('Status'),
            ])
            ->defaultSort('full_path');
    }
}

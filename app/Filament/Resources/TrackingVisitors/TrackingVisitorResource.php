<?php

namespace App\Filament\Resources\TrackingVisitors;

use App\Filament\Resources\TrackingVisitors\Pages\ListTrackingVisitors;
use App\Models\TrackingVisitor;
use App\Support\Filament\AdminNavigation;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TrackingVisitorResource extends Resource
{
    protected static ?string $model = TrackingVisitor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Tracking visitors';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigation::GROUP_TRACKING;

    protected static ?string $modelLabel = 'tracking visitor';

    protected static ?string $pluralModelLabel = 'tracking visitors';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(static::getEloquentQuery())
            ->defaultSort('last_seen_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('identifier')
                    ->label('Visitor')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('latestConsentDecision.decided_at')
                    ->label('Cookiekeuze')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('latestConsentDecision.source')
                    ->label('Bron')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::formatConsentSource($state)),
                Tables\Columns\IconColumn::make('latestConsentDecision.analytics_granted')
                    ->label('Analyse')
                    ->boolean(),
                Tables\Columns\IconColumn::make('latestConsentDecision.marketing_granted')
                    ->label('Marketing')
                    ->boolean(),
                Tables\Columns\TextColumn::make('pageview_count')
                    ->label('Paginaweergaven')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_attempt_count')
                    ->label('Contactpogingen')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_referrer')
                    ->label('Eerste referrer')
                    ->limit(42)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('first_seen_at')
                    ->label('Eerst gezien')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->label('Laatst gezien')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['latestConsentDecision']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrackingVisitors::route('/'),
        ];
    }

    private static function formatConsentSource(?string $state): string
    {
        return filled($state) ? Str::of($state)->replace('_', ' ')->headline()->value() : 'Onbekend';
    }
}

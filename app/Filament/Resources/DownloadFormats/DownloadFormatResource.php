<?php

namespace App\Filament\Resources\DownloadFormats;

use App\Filament\Resources\DownloadFormats\Pages\CreateDownloadFormat;
use App\Filament\Resources\DownloadFormats\Pages\EditDownloadFormat;
use App\Filament\Resources\DownloadFormats\Pages\ListDownloadFormats;
use App\Filament\Resources\DownloadFormats\Schemas\DownloadFormatForm;
use App\Filament\Resources\DownloadFormats\Tables\DownloadFormatsTable;
use App\Models\DownloadFormat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DownloadFormatResource extends Resource
{
    protected static ?string $model = DownloadFormat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowDown;

    protected static ?string $navigationLabel = 'Download formats';

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return DownloadFormatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DownloadFormatsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDownloadFormats::route('/'),
            'create' => CreateDownloadFormat::route('/create'),
            'edit' => EditDownloadFormat::route('/{record}/edit'),
        ];
    }
}

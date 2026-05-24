<?php

namespace App\Filament\Resources\DownloadItems;

use App\Filament\Resources\DownloadItems\Pages\CreateDownloadItem;
use App\Filament\Resources\DownloadItems\Pages\EditDownloadItem;
use App\Filament\Resources\DownloadItems\Pages\ListDownloadItems;
use App\Filament\Resources\DownloadItems\Schemas\DownloadItemForm;
use App\Filament\Resources\DownloadItems\Tables\DownloadItemsTable;
use App\Models\DownloadItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DownloadItemResource extends Resource
{
    protected static ?string $model = DownloadItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Download items';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return DownloadItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DownloadItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDownloadItems::route('/'),
            'create' => CreateDownloadItem::route('/create'),
            'edit' => EditDownloadItem::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\DownloadCategories;

use App\Filament\Resources\DownloadCategories\Pages\CreateDownloadCategory;
use App\Filament\Resources\DownloadCategories\Pages\EditDownloadCategory;
use App\Filament\Resources\DownloadCategories\Pages\ListDownloadCategories;
use App\Filament\Resources\DownloadCategories\Schemas\DownloadCategoryForm;
use App\Filament\Resources\DownloadCategories\Tables\DownloadCategoriesTable;
use App\Models\DownloadCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DownloadCategoryResource extends Resource
{
    protected static ?string $model = DownloadCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $navigationLabel = 'Downloads';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return DownloadCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DownloadCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDownloadCategories::route('/'),
            'create' => CreateDownloadCategory::route('/create'),
            'edit' => EditDownloadCategory::route('/{record}/edit'),
        ];
    }
}

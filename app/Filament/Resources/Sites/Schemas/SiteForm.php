<?php

namespace App\Filament\Resources\Sites\Schemas;

use App\Enums\SearchIndexingMode;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('domain')->required()->maxLength(255),
                TextInput::make('default_locale')->required()->default('nl')->maxLength(12),
                TagsInput::make('available_locales')->required()->default(['nl']),
                Toggle::make('is_active')->default(true),
                Select::make('search_indexing_mode')
                    ->label('Zoekmachine-indexering')
                    ->options(collect(SearchIndexingMode::cases())->mapWithKeys(
                        fn (SearchIndexingMode $mode): array => [$mode->value => $mode->label()],
                    )->all())
                    ->default(SearchIndexingMode::Index->value)
                    ->required(),
            ]);
    }
}

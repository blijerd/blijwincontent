<?php

namespace App\Filament\Resources\DownloadCategories\Schemas;

use App\Support\Filament\CmsMarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DownloadCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload(),
            TextInput::make('locale')->maxLength(12)->placeholder('nl'),
            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $state, callable $set): mixed => $set('slug', Str::slug($state))),
            TextInput::make('slug')->required()->maxLength(255),
            CmsMarkdownEditor::make(
                'intro_markdown',
                'Korte introductie boven de downloads in deze categorie.',
                '14rem',
                6000,
            ),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->label('Actief')->default(true),
        ]);
    }
}

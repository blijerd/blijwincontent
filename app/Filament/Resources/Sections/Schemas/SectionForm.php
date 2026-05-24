<?php

namespace App\Filament\Resources\Sections\Schemas;

use App\Enums\SectionType;
use App\Support\Filament\CmsMarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('page_id')->relationship('page', 'title')->required()->searchable()->preload(),
                Select::make('type')
                    ->options(collect(SectionType::cases())->mapWithKeys(fn (SectionType $type) => [$type->value => $type->name])->all())
                    ->required(),
                TextInput::make('title')->maxLength(255),
                CmsMarkdownEditor::make(
                    'intro_markdown',
                    'Introductietekst voor deze sectie. Geschikt voor koptekst, korte alineas en interne links.',
                    '16rem',
                    6000,
                ),
                TextInput::make('sort_order')->numeric()->default(0),
                Toggle::make('is_visible')->default(true),
            ]);
    }
}

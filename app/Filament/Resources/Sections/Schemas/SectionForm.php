<?php

namespace App\Filament\Resources\Sections\Schemas;

use App\Enums\SectionType;
use Filament\Forms\Components\MarkdownEditor;
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
                MarkdownEditor::make('intro_markdown')->columnSpanFull(),
                TextInput::make('sort_order')->numeric()->default(0),
                Toggle::make('is_visible')->default(true),
            ]);
    }
}

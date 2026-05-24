<?php

namespace App\Filament\Resources\FaqItems\Schemas;

use App\Support\Filament\CmsMarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FaqItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('faq_category_id')
                ->relationship('category', 'title')
                ->required()
                ->searchable()
                ->preload(),
            TextInput::make('question')->required()->maxLength(255),
            CmsMarkdownEditor::make(
                'answer_markdown',
                'Antwoord in Markdown. Gebruik korte alineas, lijsten en interne links waar logisch.',
                '20rem',
                12000,
            ),
            Toggle::make('is_featured')->label('Uitgelicht')->default(false),
            Toggle::make('is_published')->label('Gepubliceerd')->default(true),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }
}

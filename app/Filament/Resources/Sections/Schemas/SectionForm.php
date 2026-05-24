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
                Select::make('faqCategories')
                    ->label('FAQ categorieen')
                    ->relationship('faqCategories', 'title')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Alleen gebruikt wanneer het sectietype FAQ is. Items blijven centraal beheerd.'),
                TextInput::make('faq_keyword')
                    ->label('FAQ trefwoord')
                    ->maxLength(255)
                    ->placeholder('disco')
                    ->helperText('Vervangt {trefwoord} en {keyword} in vragen en antwoorden.'),
                Toggle::make('faq_searchable')->label('Zoeken toestaan')->default(true),
                Toggle::make('faq_categories_enabled')->label('Categoriefilters tonen')->default(true),
                Toggle::make('faq_schema_enabled')->label('FAQPage schema output')->default(true),
                Toggle::make('faq_expand_first')->label('Eerste vraag openklappen')->default(false),
                Toggle::make('faq_allow_multiple_open')->label('Meerdere vragen tegelijk open')->default(false),
                TextInput::make('faq_initial_limit')
                    ->label('Aantal vragen eerst tonen')
                    ->numeric()
                    ->default(0)
                    ->helperText('0 toont alle vragen.'),
                TextInput::make('faq_cta_label')->label('FAQ CTA label')->maxLength(255),
                TextInput::make('faq_cta_url')->label('FAQ CTA URL')->maxLength(255),
                TextInput::make('sort_order')->numeric()->default(0),
                Toggle::make('is_visible')->default(true),
            ]);
    }
}

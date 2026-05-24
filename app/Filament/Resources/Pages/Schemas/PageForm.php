<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\PageStatus;
use App\Enums\TemplateType;
use App\Models\Page;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Page')
                    ->tabs([
                        Tab::make('Content')
                            ->schema([
                                Select::make('site_id')->relationship('site', 'name')->required(),
                                Select::make('parent_id')
                                    ->relationship('parent', 'title')
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('locale')->required()->default('nl')->maxLength(12),
                                TextInput::make('title')->required()->maxLength(255),
                                TextInput::make('slug')->maxLength(255),
                                Select::make('template_type')
                                    ->options(collect(TemplateType::cases())->mapWithKeys(fn (TemplateType $type) => [$type->value => $type->name])->all())
                                    ->required(),
                                Select::make('status')
                                    ->options(collect(PageStatus::cases())->mapWithKeys(fn (PageStatus $status) => [$status->value => $status->name])->all())
                                    ->required()
                                    ->default(PageStatus::Draft->value),
                                DateTimePicker::make('published_at'),
                                MarkdownEditor::make('excerpt_markdown')->columnSpanFull(),
                                TextInput::make('sort_order')->numeric()->default(0),
                            ])->columns(2),
                        Tab::make('Translations')
                            ->schema([
                                TextInput::make('translation_group_id')
                                    ->helperText('Shared UUID for translated versions of the same page. Leave empty for a new group.'),
                            ]),
                        Tab::make('SEO')
                            ->schema([
                                TextInput::make('seo_title')->maxLength(255),
                                TextInput::make('seo_description')->maxLength(500),
                                TextInput::make('og_title')->maxLength(255),
                                TextInput::make('og_description')->maxLength(500),
                                TextInput::make('canonical_url')->url()->maxLength(255),
                                Toggle::make('robots_index')->default(true),
                                Toggle::make('robots_follow')->default(true),
                            ])->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

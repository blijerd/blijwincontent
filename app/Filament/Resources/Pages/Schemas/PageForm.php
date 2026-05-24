<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\PageStatus;
use App\Enums\TemplateType;
use App\Filament\Resources\Pages\Tables\PageParentSelectTable;
use App\Models\Page;
use App\Support\Filament\CmsMarkdownEditor;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\ModalTableSelect;
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
                                ModalTableSelect::make('parent_id')
                                    ->label('Bovenliggende pagina')
                                    ->placeholder('Geen bovenliggende pagina')
                                    ->relationship('parent', 'title')
                                    ->tableConfiguration(PageParentSelectTable::class)
                                    ->getOptionLabelFromRecordUsing(fn (Page $record): string => $record->full_path ?: $record->title)
                                    ->selectAction(
                                        fn (Action $action): Action => $action
                                            ->label('Pagina kiezen')
                                            ->modalHeading('Kies bovenliggende pagina')
                                            ->modalSubmitActionLabel('Kiezen'),
                                    ),
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
                                CmsMarkdownEditor::make(
                                    'excerpt_markdown',
                                    'Korte pagina-intro in Markdown voor samenvattingen, lijstweergaven en SEO-context.',
                                    '14rem',
                                    3000,
                                ),
                                TextInput::make('sort_order')->numeric()->default(0),
                                Toggle::make('is_routable')->label('Publiek bereikbaar')->default(true),
                                Toggle::make('is_visible_in_navigation')->label('Beschikbaar voor navigatie')->default(true),
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

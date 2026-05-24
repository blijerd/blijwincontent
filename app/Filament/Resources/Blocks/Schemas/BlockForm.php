<?php

namespace App\Filament\Resources\Blocks\Schemas;

use App\Enums\BlockType;
use App\Support\Filament\CmsMarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('section_id')->relationship('section', 'title')->required()->searchable()->preload(),
                Select::make('type')
                    ->options(collect(BlockType::cases())->mapWithKeys(fn (BlockType $type) => [$type->value => $type->name])->all())
                    ->required(),
                TextInput::make('sort_order')->numeric()->default(0),
                TextInput::make('heading')->maxLength(255),
                TextInput::make('subheading')->maxLength(255),
                CmsMarkdownEditor::make(
                    'body_markdown',
                    'Hoofdcontent in Markdown. Gebruik headings bewust voor semantische HTML en SEO.',
                    '22rem',
                    20000,
                ),
                TextInput::make('button_label')->maxLength(255),
                TextInput::make('button_url')->maxLength(255),
                Select::make('image_id')->relationship('image', 'original_filename')->searchable()->preload(),
            ]);
    }
}

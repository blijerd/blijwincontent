<?php

namespace App\Filament\Resources\DownloadItems\Schemas;

use App\Support\Filament\CmsMarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DownloadItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('download_category_id')
                ->label('Categorie')
                ->relationship('category', 'title')
                ->required()
                ->searchable()
                ->preload(),
            TextInput::make('title')->required()->maxLength(255),
            CmsMarkdownEditor::make(
                'preview_markdown',
                'Korte previewtekst voor de kaart. Markdown wordt veilig gerenderd.',
                '16rem',
                8000,
            ),
            Select::make('preview_image_id')
                ->label('Preview afbeelding')
                ->relationship('previewImage', 'original_filename')
                ->searchable()
                ->preload(),
            TextInput::make('preview_image_alt')->label('Alt-tekst afbeelding')->maxLength(255),
            TextInput::make('preview_image_focus')->label('Afbeelding focus')->maxLength(80)->placeholder('center center'),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->label('Actief')->default(true),
        ]);
    }
}

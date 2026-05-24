<?php

namespace App\Filament\Resources\DownloadFormats\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DownloadFormatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('download_item_id')
                ->label('Download')
                ->relationship('item', 'title')
                ->required()
                ->searchable()
                ->preload(),
            TextInput::make('label')->required()->maxLength(255)->placeholder('PDF'),
            TextInput::make('file_path')
                ->required()
                ->maxLength(255)
                ->helperText('Gebruik een public disk pad zoals downloads/brochure.pdf, een pagina-URL of externe URL.'),
            Toggle::make('is_secure')->label('Per e-mail beveiligen')->default(false),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }
}

<?php

namespace App\Filament\Resources\MediaAssets\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MediaAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('path')->disk('public')->directory('media')->required(),
                TextInput::make('disk')->required()->default('public'),
                TextInput::make('mime_type')->required()->maxLength(150),
                TextInput::make('original_filename')->required()->maxLength(255),
                TextInput::make('size')->numeric()->required(),
                TextInput::make('width')->numeric(),
                TextInput::make('height')->numeric(),
                TextInput::make('alt_text')->maxLength(255),
                TextInput::make('locale')->maxLength(12),
            ]);
    }
}

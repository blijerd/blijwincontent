<?php

namespace App\Filament\Resources\Redirects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site_id')->relationship('site', 'name')->required(),
                TextInput::make('source_path')->required()->maxLength(255),
                TextInput::make('target_url')->required()->maxLength(255),
                Select::make('status_code')->options([301 => '301', 302 => '302', 307 => '307', 308 => '308'])->default(301)->required(),
                TextInput::make('locale')->maxLength(12),
            ]);
    }
}

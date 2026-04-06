<?php

namespace App\Filament\Resources\Medals\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MedalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                FileUpload::make('image')
                    ->image()
                    ->required(),
                TextInput::make('description')
                    ->required(),
                TextInput::make('wiki_id')
                    ->required()
                    ->numeric(),
            ]);
    }
}

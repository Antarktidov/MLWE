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
                    ->required()
                    ->disk('public')
                    ->visibility('public')
                    ->imageResizeMode('auto')
                    ->imagePreviewHeight('150') // Установите фиксированную высоту превью
                    ->imageCropAspectRatio('16:9') // Если нужен кроп, укажите его явно
                    // ->removeUploadedFileButton() // Отключает кнопку "Удалить", если не нужна
                    ->downloadable(false) // Отключает кнопку скачивания
                    ->openable(false), // Отключает возможность открыть изображение в модальном окне
                TextInput::make('description')
                    ->required(),
                TextInput::make('wiki_id')
                    ->required()
                    ->numeric(),
            ]);
    }
}

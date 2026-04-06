<?php

namespace App\Filament\Resources\Medals\Pages;

use App\Filament\Resources\Medals\MedalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedals extends ListRecords
{
    protected static string $resource = MedalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

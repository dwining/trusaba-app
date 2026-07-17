<?php

namespace App\Filament\Resources\Travellers\Pages;

use App\Filament\Resources\Travellers\TravellerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTravellers extends ListRecords
{
    protected static string $resource = TravellerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

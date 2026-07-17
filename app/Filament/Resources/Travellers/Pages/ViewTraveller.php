<?php

namespace App\Filament\Resources\Travellers\Pages;

use App\Filament\Resources\Travellers\TravellerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTraveller extends ViewRecord
{
    protected static string $resource = TravellerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\Travellers\Pages;

use App\Filament\Resources\Travellers\TravellerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTraveller extends EditRecord
{
    protected static string $resource = TravellerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

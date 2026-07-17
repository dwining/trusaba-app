<?php

namespace App\Filament\Resources\Officers\Pages;

use App\Filament\Resources\Officers\OfficerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOfficer extends ViewRecord
{
    protected static string $resource = OfficerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

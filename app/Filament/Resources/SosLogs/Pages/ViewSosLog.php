<?php

namespace App\Filament\Resources\SosLogs\Pages;

use App\Filament\Resources\SosLogs\SosLogResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSosLog extends ViewRecord
{
    protected static string $resource = SosLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

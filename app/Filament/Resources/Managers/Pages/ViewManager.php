<?php

namespace App\Filament\Resources\Managers\Pages;

use App\Filament\Resources\Managers\ManagerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewManager extends ViewRecord
{
    protected static string $resource = ManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

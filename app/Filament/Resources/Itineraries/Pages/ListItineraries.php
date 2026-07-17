<?php

namespace App\Filament\Resources\Itineraries\Pages;

use App\Filament\Resources\Itineraries\ItineraryResource;
use Filament\Resources\Pages\ListRecords;

class ListItineraries extends ListRecords
{
    protected static string $resource = ItineraryResource::class;
}

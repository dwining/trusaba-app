<?php

namespace App\Filament\Merchant\Resources\BookingResource\Pages;

use App\Filament\Merchant\Resources\BookingResource\BookingResource;
use Filament\Resources\Pages\ListRecords;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;
}

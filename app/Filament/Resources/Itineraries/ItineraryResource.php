<?php

namespace App\Filament\Resources\Itineraries;

use App\Filament\Resources\Itineraries\Pages\ListItineraries;
use App\Filament\Resources\Itineraries\Pages\ViewItinerary;
use App\Filament\Resources\Itineraries\Tables\ItinerariesTable;
use App\Models\Itinerary;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ItineraryResource extends Resource
{
    protected static ?string $model = Itinerary::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    public static function table(Table $table): Table
    {
        return ItinerariesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItineraries::route('/'),
            'view' => ViewItinerary::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

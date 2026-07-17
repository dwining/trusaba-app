<?php

namespace App\Filament\Resources\Travellers\Schemas;

use Filament\Schemas\Components\BooleanEntry;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

class TravellerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email'),
                BooleanEntry::make('is_active'),
                TextEntry::make('role'),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}

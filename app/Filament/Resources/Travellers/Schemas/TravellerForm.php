<?php

namespace App\Filament\Resources\Travellers\Schemas;

use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;

class TravellerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->default(true),
                Select::make('role')
                    ->options([
                        'traveller' => 'Traveller',
                    ])
                    ->default('traveller')
                    ->disabled()
                    ->dehydrated()
                    ->required(),
            ]);
    }
}

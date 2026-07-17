<?php

namespace App\Filament\Resources\Officers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OfficerForm
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
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->default(true),
                Select::make('role')
                    ->options([
                        'officer' => 'Officer',
                    ])
                    ->default('officer')
                    ->disabled()
                    ->dehydrated()
                    ->required(),
            ]);
    }
}

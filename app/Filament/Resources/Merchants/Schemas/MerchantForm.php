<?php

namespace App\Filament\Resources\Merchants\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MerchantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->options([
                        'hotel' => 'Hotel',
                        'restaurant' => 'Restaurant',
                        'cafe' => 'Cafe',
                        'attraction' => 'Attraction',
                        'transport' => 'Transport',
                        'other' => 'Other',
                    ])
                    ->required(),
                Textarea::make('address')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('city')
                    ->required(),
                TextInput::make('province')
                    ->required(),
                TextInput::make('country')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TagsInput::make('profile_tags')
                    ->separator(',')
                    ->suggestions([
                        'beach',
                        'nature',
                        'culture',
                        'culinary',
                        'surfing',
                        'hiking',
                        'shopping',
                        'photography',
                        'wellness',
                        'adventure',
                        'family',
                        'luxury',
                        'budget',
                    ])
                    ->columnSpanFull(),
                TextInput::make('logo'),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('wallet_balance')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}

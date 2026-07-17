<?php

namespace App\Filament\Resources\Merchants\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MerchantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('name'),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('address')
                    ->columnSpanFull(),
                TextEntry::make('city'),
                TextEntry::make('province'),
                TextEntry::make('country'),
                TextEntry::make('phone'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('logo')
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('wallet_balance')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

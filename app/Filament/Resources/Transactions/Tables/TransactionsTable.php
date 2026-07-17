<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Traveller')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('booking.merchant.name')
                    ->label('Merchant')
                    ->searchable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}

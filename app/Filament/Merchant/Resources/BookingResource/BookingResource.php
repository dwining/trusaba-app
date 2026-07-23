<?php

namespace App\Filament\Merchant\Resources\BookingResource;

use App\Filament\Merchant\Resources\BookingResource\Pages\ListBookings;
use App\Filament\Merchant\Resources\BookingResource\Pages\ViewBooking;
use App\Models\Booking;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingResource extends Resource
{
    protected static ?string $slug = 'bookings';

    protected static ?string $model = Booking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Booking Details')
                    ->schema([
                        TextEntry::make('voucher_code')
                            ->label('Voucher Code'),
                        TextEntry::make('booking_type')
                            ->label('Booking Type'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                        TextEntry::make('user.name')
                            ->label('Traveller'),
                        TextEntry::make('user.email')
                            ->label('Email'),
                        TextEntry::make('check_in_date')
                            ->label('Check-in')
                            ->date(),
                        TextEntry::make('check_out_date')
                            ->label('Check-out')
                            ->date(),
                        TextEntry::make('booking_date')
                            ->label('Booking Date')
                            ->date(),
                        TextEntry::make('quantity')
                            ->label('Quantity'),
                        TextEntry::make('amount')
                            ->label('Total')
                            ->numeric(),
                        TextEntry::make('notes')
                            ->label('Notes'),
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                    ])
                    ->columns(2),
                Section::make('Resource Details')
                    ->schema([
                        TextEntry::make('resource_detail')
                            ->label('Resource Detail')
                            ->json(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('voucher_code')
                    ->label('Voucher')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('booking_type')
                    ->label('Type')
                    ->sortable(),
                TextColumn::make('check_in_date')
                    ->label('Check-in')
                    ->date()
                    ->sortable(),
                TextColumn::make('check_out_date')
                    ->label('Check-out')
                    ->date()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'checked_in' => 'Checked In',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $merchantId = auth()->user()->merchant?->id;

        return parent::getEloquentQuery()->where('merchant_id', $merchantId);
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
            'index' => ListBookings::route('/'),
            'view' => ViewBooking::route('/{record}'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Orders';
    }
}

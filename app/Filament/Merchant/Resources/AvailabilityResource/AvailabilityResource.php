<?php

namespace App\Filament\Merchant\Resources\AvailabilityResource;

use App\Filament\Merchant\Resources\AvailabilityResource\Pages\ListAvailabilities;
use App\Models\MerchantAvailability;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AvailabilityResource extends Resource
{
    protected static ?string $slug = 'availabilities';

    protected static ?string $model = MerchantAvailability::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $label = 'Availability';

    protected static ?string $pluralLabel = 'Availability';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('resource_type')
                    ->label('Resource Type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('resource_id')
                    ->label('Resource ID')
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('available_qty')
                    ->label('Available')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('date', 'asc');
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
            'index' => ListAvailabilities::route('/'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Inventory';
    }
}

<?php

namespace App\Filament\Merchant\Resources\VehicleResource;

use App\Filament\Merchant\Resources\VehicleResource\Pages\ListVehicles;
use App\Models\MerchantVehicle;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VehicleResource extends Resource
{
    protected static ?string $slug = 'vehicles';

    protected static ?string $model = MerchantVehicle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $label = 'Vehicle';

    protected static ?string $pluralLabel = 'Vehicles';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('vehicle_type')
                    ->label('Vehicle Type')
                    ->required(),
                TextInput::make('vehicle_name')
                    ->label('Vehicle Name')
                    ->required(),
                TextInput::make('total_units')
                    ->label('Total Units')
                    ->numeric()
                    ->required(),
                TextInput::make('price_per_day')
                    ->label('Price per Day')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle_type')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vehicle_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_units')
                    ->label('Total Units')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price_per_day')
                    ->label('Price /Day')
                    ->numeric()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
            ]);
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
            'index' => ListVehicles::route('/'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Inventory';
    }
}

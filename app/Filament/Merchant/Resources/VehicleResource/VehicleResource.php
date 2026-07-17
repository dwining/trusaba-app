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

    protected static ?string $label = 'Kendaraan';

    protected static ?string $pluralLabel = 'Kendaraan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('vehicle_type')
                    ->label('Tipe Kendaraan')
                    ->required(),
                TextInput::make('vehicle_name')
                    ->label('Nama Kendaraan')
                    ->required(),
                TextInput::make('total_units')
                    ->label('Total Unit')
                    ->numeric()
                    ->required(),
                TextInput::make('price_per_day')
                    ->label('Harga per Hari')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle_type')
                    ->label('Tipe')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vehicle_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_units')
                    ->label('Total Unit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price_per_day')
                    ->label('Harga /Hari')
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
        return 'Inventori';
    }
}

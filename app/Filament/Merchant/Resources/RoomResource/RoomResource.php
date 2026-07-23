<?php

namespace App\Filament\Merchant\Resources\RoomResource;

use App\Filament\Merchant\Resources\RoomResource\Pages\ListRooms;
use App\Models\MerchantRoom;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoomResource extends Resource
{
    protected static ?string $slug = 'rooms';

    protected static ?string $model = MerchantRoom::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $label = 'Room';

    protected static ?string $pluralLabel = 'Rooms';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('room_type')
                    ->label('Room Type')
                    ->required(),
                TextInput::make('total_rooms')
                    ->label('Total Rooms')
                    ->numeric()
                    ->required(),
                TextInput::make('price_per_night')
                    ->label('Price per Night')
                    ->numeric()
                    ->required(),
                Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room_type')
                    ->label('Room Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_rooms')
                    ->label('Total Rooms')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price_per_night')
                    ->label('Price /Night')
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
            'index' => ListRooms::route('/'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Inventory';
    }
}

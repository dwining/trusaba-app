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

    protected static ?string $label = 'Kamar';

    protected static ?string $pluralLabel = 'Kamar';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('room_type')
                    ->label('Tipe Kamar')
                    ->required(),
                TextInput::make('total_rooms')
                    ->label('Total Kamar')
                    ->numeric()
                    ->required(),
                TextInput::make('price_per_night')
                    ->label('Harga per Malam')
                    ->numeric()
                    ->required(),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room_type')
                    ->label('Tipe Kamar')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_rooms')
                    ->label('Total Kamar')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price_per_night')
                    ->label('Harga /Malam')
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
        return 'Inventori';
    }
}

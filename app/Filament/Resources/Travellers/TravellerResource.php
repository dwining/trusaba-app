<?php

namespace App\Filament\Resources\Travellers;

use App\Filament\Resources\Travellers\Pages\CreateTraveller;
use App\Filament\Resources\Travellers\Pages\EditTraveller;
use App\Filament\Resources\Travellers\Pages\ListTravellers;
use App\Filament\Resources\Travellers\Pages\ViewTraveller;
use App\Filament\Resources\Travellers\Schemas\TravellerForm;
use App\Filament\Resources\Travellers\Schemas\TravellerInfolist;
use App\Filament\Resources\Travellers\Tables\TravellersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TravellerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Traveller';

    protected static ?string $pluralModelLabel = 'Travellers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TravellerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TravellerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TravellersTable::configure($table);
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
            'index' => ListTravellers::route('/'),
            'create' => CreateTraveller::route('/create'),
            'view' => ViewTraveller::route('/{record}'),
            'edit' => EditTraveller::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'traveller');
    }
}

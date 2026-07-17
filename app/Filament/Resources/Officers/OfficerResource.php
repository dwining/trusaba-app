<?php

namespace App\Filament\Resources\Officers;

use App\Filament\Resources\Officers\Pages\CreateOfficer;
use App\Filament\Resources\Officers\Pages\EditOfficer;
use App\Filament\Resources\Officers\Pages\ListOfficers;
use App\Filament\Resources\Officers\Pages\ViewOfficer;
use App\Filament\Resources\Officers\Schemas\OfficerForm;
use App\Filament\Resources\Officers\Tables\OfficersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OfficerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Officer';

    protected static ?string $pluralModelLabel = 'Officers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    public static function form(Schema $schema): Schema
    {
        return OfficerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficersTable::configure($table);
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
            'index' => ListOfficers::route('/'),
            'create' => CreateOfficer::route('/create'),
            'view' => ViewOfficer::route('/{record}'),
            'edit' => EditOfficer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'officer');
    }
}

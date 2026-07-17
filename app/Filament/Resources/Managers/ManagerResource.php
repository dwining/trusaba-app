<?php

namespace App\Filament\Resources\Managers;

use App\Filament\Resources\Managers\Pages\CreateManager;
use App\Filament\Resources\Managers\Pages\EditManager;
use App\Filament\Resources\Managers\Pages\ListManagers;
use App\Filament\Resources\Managers\Pages\ViewManager;
use App\Filament\Resources\Managers\Schemas\ManagerForm;
use App\Filament\Resources\Managers\Tables\ManagersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManagerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Manager';

    protected static ?string $pluralModelLabel = 'Managers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function form(Schema $schema): Schema
    {
        return ManagerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManagersTable::configure($table);
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
            'index' => ListManagers::route('/'),
            'create' => CreateManager::route('/create'),
            'view' => ViewManager::route('/{record}'),
            'edit' => EditManager::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'manager');
    }
}

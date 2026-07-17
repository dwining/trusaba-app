<?php

namespace App\Filament\Resources\SosLogs;

use App\Filament\Resources\SosLogs\Pages\ListSosLogs;
use App\Filament\Resources\SosLogs\Pages\ViewSosLog;
use App\Filament\Resources\SosLogs\Tables\SosLogsTable;
use App\Models\SosLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SosLogResource extends Resource
{
    protected static ?string $model = SosLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    public static function table(Table $table): Table
    {
        return SosLogsTable::configure($table);
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
            'index' => ListSosLogs::route('/'),
            'view' => ViewSosLog::route('/{record}'),
        ];
    }
}

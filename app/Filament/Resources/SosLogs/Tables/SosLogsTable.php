<?php

namespace App\Filament\Resources\SosLogs\Tables;

use App\Models\SosLog;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SosLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Traveller')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('message')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('handle')
                    ->label('Handle')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (SosLog $record): void {
                        $record->update([
                            'status' => 'handled',
                            'handled_by' => Auth::id(),
                            'handled_at' => now(),
                        ]);
                    })
                    ->visible(fn (SosLog $record): bool => $record->status === 'open')
                    ->requiresConfirmation(),
            ]);
    }
}

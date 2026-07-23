<?php

namespace App\Filament\Merchant\Resources\BookingResource\Pages;

use App\Filament\Merchant\Resources\BookingResource\BookingResource;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Booking $record */
        $record = $this->record;
        $actions = [];

        if ($record->status === 'pending') {
            $actions[] = Action::make('confirm')
                ->label('Confirm')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => 'confirmed']);
                    Notification::make()
                        ->success()
                        ->title('Booking confirmed.')
                        ->send();
                    $this->refreshFormData(['status']);
                });

            $actions[] = Action::make('cancel')
                ->label('Cancel')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => 'cancelled']);
                    Notification::make()
                        ->warning()
                        ->title('Booking cancelled.')
                        ->send();
                    $this->refreshFormData(['status']);
                });
        }

        if ($record->status === 'confirmed') {
            $actions[] = Action::make('checkin')
                ->label('Check-in')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('warning')
                ->form([
                    TextInput::make('voucher_code')
                        ->label('Voucher Code')
                        ->required()
                        ->default($record->voucher_code),
                ])
                ->action(function (array $data) use ($record): void {
                    if ($data['voucher_code'] !== $record->voucher_code) {
                        Notification::make()
                            ->danger()
                            ->title('Invalid voucher code.')
                            ->send();

                        return;
                    }

                    $record->update(['status' => 'checked_in']);
                    Notification::make()
                        ->success()
                        ->title('Check-in successful.')
                        ->send();
                    $this->refreshFormData(['status']);
                });

            $actions[] = Action::make('cancel')
                ->label('Cancel')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => 'cancelled']);
                    Notification::make()
                        ->warning()
                        ->title('Booking cancelled.')
                        ->send();
                    $this->refreshFormData(['status']);
                });
        }

        if ($record->status === 'checked_in') {
            $actions[] = Action::make('complete')
                ->label('Complete')
                ->icon('heroicon-o-flag')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => 'completed']);
                    Notification::make()
                        ->success()
                        ->title('Booking completed.')
                        ->send();
                    $this->refreshFormData(['status']);
                });
        }

        return $actions;
    }
}

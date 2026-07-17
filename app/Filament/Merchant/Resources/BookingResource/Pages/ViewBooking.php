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
                ->label('Konfirmasi')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => 'confirmed']);
                    Notification::make()
                        ->success()
                        ->title('Booking dikonfirmasi.')
                        ->send();
                    $this->refreshFormData(['status']);
                });

            $actions[] = Action::make('cancel')
                ->label('Batalkan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => 'cancelled']);
                    Notification::make()
                        ->warning()
                        ->title('Booking dibatalkan.')
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
                        ->label('Kode Voucher')
                        ->required()
                        ->default($record->voucher_code),
                ])
                ->action(function (array $data) use ($record): void {
                    if ($data['voucher_code'] !== $record->voucher_code) {
                        Notification::make()
                            ->danger()
                            ->title('Kode voucher tidak valid.')
                            ->send();

                        return;
                    }

                    $record->update(['status' => 'checked_in']);
                    Notification::make()
                        ->success()
                        ->title('Check-in berhasil.')
                        ->send();
                    $this->refreshFormData(['status']);
                });

            $actions[] = Action::make('cancel')
                ->label('Batalkan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => 'cancelled']);
                    Notification::make()
                        ->warning()
                        ->title('Booking dibatalkan.')
                        ->send();
                    $this->refreshFormData(['status']);
                });
        }

        if ($record->status === 'checked_in') {
            $actions[] = Action::make('complete')
                ->label('Selesaikan')
                ->icon('heroicon-o-flag')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update(['status' => 'completed']);
                    Notification::make()
                        ->success()
                        ->title('Booking selesai.')
                        ->send();
                    $this->refreshFormData(['status']);
                });
        }

        return $actions;
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Itinerary;
use App\Models\Merchant;
use App\Models\SosLog;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Traveller', User::where('role', 'traveller')->count())
                ->icon('heroicon-o-user-group'),
            Stat::make('Total Merchant', Merchant::count())
                ->icon('heroicon-o-building-storefront'),
            Stat::make('Total Itinerary', Itinerary::count())
                ->icon('heroicon-o-map'),
            Stat::make('Total Transaksi', Transaction::where('status', 'paid')->count())
                ->description('Lunas')
                ->icon('heroicon-o-banknotes'),
            Stat::make('SOS Aktif', SosLog::where('status', 'open')->count())
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),
        ];
    }
}

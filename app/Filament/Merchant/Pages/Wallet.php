<?php

namespace App\Filament\Merchant\Pages;

use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

/**
 * @property Collection<WalletTransaction> $transactions
 * @property Collection<WithdrawalRequest> $withdrawals
 */
class Wallet extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected string $view = 'filament.merchant.pages.wallet';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    /** @var Collection<WalletTransaction> */
    public Collection $transactions;

    /** @var Collection<WithdrawalRequest> */
    public Collection $withdrawals;

    public float $balance = 0;

    public function mount(): void
    {
        $merchant = auth()->user()->merchant;
        $this->balance = $merchant?->wallet_balance ?? 0;
        $this->transactions = WalletTransaction::where('merchant_id', $merchant?->id)
            ->latest()->take(20)->get();
        $this->withdrawals = WithdrawalRequest::where('merchant_id', $merchant?->id)
            ->latest()->take(10)->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('withdraw')
                ->label('Request Withdrawal')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    TextInput::make('amount')
                        ->label('Jumlah (Rp)')
                        ->numeric()
                        ->required()
                        ->minValue(10000),
                    TextInput::make('bank_name')
                        ->label('Nama Bank')
                        ->required(),
                    TextInput::make('account_number')
                        ->label('No. Rekening')
                        ->required(),
                    TextInput::make('account_name')
                        ->label('Nama Pemilik Rekening')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $merchant = auth()->user()->merchant;

                    if ($data['amount'] > $merchant->wallet_balance) {
                        Notification::make()
                            ->danger()
                            ->title('Saldo tidak mencukupi.')
                            ->send();

                        return;
                    }

                    WithdrawalRequest::create([
                        'merchant_id' => $merchant->id,
                        'amount' => $data['amount'],
                        'bank_name' => $data['bank_name'],
                        'account_number' => $data['account_number'],
                        'account_name' => $data['account_name'],
                        'status' => 'pending',
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Withdrawal request terkirim.')
                        ->send();

                    $this->mount();
                }),
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\MerchantRoom;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@trusaba.id'],
            [
                'name' => 'Admin TruSaba',
                'password' => Hash::make('admin123'),
                'role' => 'superadmin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        $merchantUser = User::firstOrCreate(
            ['email' => 'hotel@trusaba.id'],
            [
                'name' => 'Seminyak Palm Hotel',
                'role' => 'merchant',
                'password' => bcrypt('password'),
            ]
        );

        $merchant = Merchant::firstOrCreate(
            ['user_id' => $merchantUser->id],
            [
                'name' => 'Seminyak Palm Hotel',
                'type' => 'hotel',
                'address' => 'Jl. Kayu Aya No. 18, Seminyak, Bali',
                'city' => 'Badung',
                'province' => 'Bali',
                'country' => 'Indonesia',
                'phone' => '0361-123456',
                'description' => 'Hotel bintang 4 dengan pool view di pusat Seminyak.',
                'is_active' => true,
                'wallet_balance' => 5000000,
            ]
        );

        // Seed test wallet transactions for merchant
        WalletTransaction::firstOrCreate(
            ['merchant_id' => $merchant->id, 'type' => 'credit', 'amount' => 2000000],
            [
                'balance_after' => 2000000,
                'description' => 'Booking #BOOK-001 - Deluxe Room 2 malam',
                'available_at' => now()->subDays(3),
            ]
        );

        WalletTransaction::firstOrCreate(
            ['merchant_id' => $merchant->id, 'type' => 'credit', 'amount' => 3500000],
            [
                'balance_after' => 5500000,
                'description' => 'Booking #BOOK-002 - Suite Room 3 malam',
                'available_at' => now()->subDay(),
            ]
        );

        WalletTransaction::firstOrCreate(
            ['merchant_id' => $merchant->id, 'type' => 'debit', 'amount' => 500000],
            [
                'balance_after' => 5000000,
                'description' => 'Withdrawal ke Bank BCA',
                'available_at' => now()->subDays(2),
            ]
        );

        // Seed test rooms for merchant
        MerchantRoom::firstOrCreate(
            ['merchant_id' => $merchant->id, 'room_type' => 'Deluxe'],
            [
                'total_rooms' => 10,
                'price_per_night' => 750000,
                'description' => 'Kamar Deluxe dengan view taman, AC, TV, WiFi',
            ]
        );

        MerchantRoom::firstOrCreate(
            ['merchant_id' => $merchant->id, 'room_type' => 'Suite'],
            [
                'total_rooms' => 5,
                'price_per_night' => 1500000,
                'description' => 'Suite room dengan bathtub, balkon, dan pemandangan kolam renang',
            ]
        );
    }
}

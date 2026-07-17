<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\MerchantAvailability;
use App\Models\MerchantRoom;
use App\Models\MerchantVehicle;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ──── Admin / test user ────
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

        // ──── Clean-up existing seed data (idempotent re-run safety) ────
        $seedEmails = [
            'hotel-seminyak@trusaba.id',
            'hotel-ubud@trusaba.id',
            'resto-bebekbengil@trusaba.id',
            'resto-makbeng@trusaba.id',
            'hotel-kuta@trusaba.id',
            'tour-nusapenida@trusaba.id',
            'transport-baliprivate@trusaba.id',
            'shop-pasarsukawati@trusaba.id',
            'hotel-malioboro@trusaba.id',
            'resto-gudegyudjum@trusaba.id',
            'tour-prambanan@trusaba.id',
            'hotel-dago@trusaba.id',
            'resto-lela@trusaba.id',
            'tour-kawahputih@trusaba.id',
            'hotel-senggigi@trusaba.id',
            'tour-gilit@trusaba.id',
        ];

        // Remove stale merchant-seed users so they can be re-created
        User::whereIn('email', $seedEmails)->delete();

        // Also clean up the old firstOrCreate merchant if it exists (old email)
        User::where('email', 'hotel@trusaba.id')->delete();

        // ══════════════════════════════════════════════════════════════════════
        // BALI
        // ══════════════════════════════════════════════════════════════════════

        // 1 ── Seminyak Palm Hotel ───────────────────────────────────────────
        $u = User::factory()->create([
            'name' => 'Seminyak Palm Hotel',
            'email' => 'hotel-seminyak@trusaba.id',
            'role' => 'merchant',
        ]);

        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Seminyak Palm Hotel',
            'type' => 'hotel',
            'address' => 'Jl. Kayu Aya No. 18, Seminyak',
            'city' => 'Badung',
            'province' => 'Bali',
            'country' => 'Indonesia',
            'phone' => '0361-930123',
            'description' => 'Hotel bintang 4 di pusat Seminyak, dekat Pantai Double Six.',
            'is_active' => true,
            'wallet_balance' => 5000000,
        ]);

        $r1 = MerchantRoom::create(['merchant_id' => $m->id, 'room_type' => 'Superior Twin', 'total_rooms' => 5, 'price_per_night' => 1100000, 'description' => '2 twin bed, 24m², balcony']);
        $r2 = MerchantRoom::create(['merchant_id' => $m->id, 'room_type' => 'Deluxe King', 'total_rooms' => 3, 'price_per_night' => 1450000, 'description' => '1 king bed, 32m², pool view']);
        $r3 = MerchantRoom::create(['merchant_id' => $m->id, 'room_type' => 'Garden Suite', 'total_rooms' => 2, 'price_per_night' => 1900000, 'description' => 'Suite with private garden, 45m², bathtub']);

        $this->seedRoomAvailability($m, [$r1, $r2, $r3]);

        // Seed test wallet transactions for merchant 1
        WalletTransaction::firstOrCreate(
            ['merchant_id' => $m->id, 'type' => 'credit', 'amount' => 2000000],
            [
                'balance_after' => 2000000,
                'description' => 'Booking #BOOK-001 - Deluxe Room 2 malam',
                'available_at' => now()->subDays(3),
            ]
        );
        WalletTransaction::firstOrCreate(
            ['merchant_id' => $m->id, 'type' => 'credit', 'amount' => 3500000],
            [
                'balance_after' => 5500000,
                'description' => 'Booking #BOOK-002 - Suite Room 3 malam',
                'available_at' => now()->subDay(),
            ]
        );
        WalletTransaction::firstOrCreate(
            ['merchant_id' => $m->id, 'type' => 'debit', 'amount' => 500000],
            [
                'balance_after' => 5000000,
                'description' => 'Withdrawal ke Bank BCA',
                'available_at' => now()->subDays(2),
            ]
        );

        // 2 ── Ubud Village Resort ───────────────────────────────────────────
        $u = User::factory()->create(['name' => 'Ubud Village Resort', 'email' => 'hotel-ubud@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Ubud Village Resort',
            'type' => 'hotel',
            'address' => 'Jl. Raya Ubud No. 88',
            'city' => 'Gianyar',
            'province' => 'Bali',
            'country' => 'Indonesia',
            'phone' => '0361-975888',
            'description' => 'Resort tenang di tengah Ubud dengan pemandangan sawah yang indah.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);

        $r1 = MerchantRoom::create(['merchant_id' => $m->id, 'room_type' => 'Standard Room', 'total_rooms' => 8, 'price_per_night' => 750000, 'description' => 'Standard room, 20m², garden view']);
        $r2 = MerchantRoom::create(['merchant_id' => $m->id, 'room_type' => 'Rice Field View', 'total_rooms' => 4, 'price_per_night' => 950000, 'description' => 'Superior room, 28m², rice field view, balcony']);
        $this->seedRoomAvailability($m, [$r1, $r2]);

        // 3 ── Bebek Bengil ──────────────────────────────────────────────────
        $u = User::factory()->create(['name' => 'Bebek Bengil', 'email' => 'resto-bebekbengil@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Bebek Bengil',
            'type' => 'restaurant',
            'address' => 'Jl. Hanoman, Ubud',
            'city' => 'Gianyar',
            'province' => 'Bali',
            'country' => 'Indonesia',
            'phone' => '0361-975489',
            'description' => 'Restoran ikonik di Ubud dengan menu bebek goreng renyah dan pemandangan sawah yang menenangkan.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);

        // 4 ── Warung Mak Beng ───────────────────────────────────────────────
        $u = User::factory()->create(['name' => 'Warung Mak Beng', 'email' => 'resto-makbeng@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Warung Mak Beng',
            'type' => 'restaurant',
            'address' => 'Jl. Hang Tuah, Sanur',
            'city' => 'Denpasar',
            'province' => 'Bali',
            'country' => 'Indonesia',
            'phone' => '0361-282633',
            'description' => 'Warung seafood legendaris di Sanur, terkenal dengan sup ikan dan sambal terasi khas Bali.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);

        // 5 ── Kuta Beach Inn ────────────────────────────────────────────────
        $u = User::factory()->create(['name' => 'Kuta Beach Inn', 'email' => 'hotel-kuta@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Kuta Beach Inn',
            'type' => 'hotel',
            'address' => 'Jl. Pantai Kuta, Kuta',
            'city' => 'Badung',
            'province' => 'Bali',
            'country' => 'Indonesia',
            'phone' => '0361-755678',
            'description' => 'Hotel terjangkau di pusat Kuta, hanya 2 menit jalan kaki ke Pantai Kuta.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);

        $r1 = MerchantRoom::create(['merchant_id' => $m->id, 'room_type' => 'Standard', 'total_rooms' => 10, 'price_per_night' => 450000, 'description' => 'Standard room, 18m², AC, TV']);
        $r2 = MerchantRoom::create(['merchant_id' => $m->id, 'room_type' => 'Ocean View', 'total_rooms' => 5, 'price_per_night' => 650000, 'description' => 'Ocean view room, 22m², balcony']);
        $this->seedRoomAvailability($m, [$r1, $r2]);

        // 6 ── Nusa Penida Day Tour ──────────────────────────────────────────
        $u = User::factory()->create(['name' => 'Nusa Penida Day Tour', 'email' => 'tour-nusapenida@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Nusa Penida Day Tour',
            'type' => 'attraction',
            'address' => 'Nusa Penida',
            'city' => 'Klungkung',
            'province' => 'Bali',
            'country' => 'Indonesia',
            'phone' => '0812-34567890',
            'description' => 'Paket tour sehari ke Nusa Penida: Kelingking Beach, Broken Beach, Angel\'s Billabong. Termasuk fast boat PP dan makan siang.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);

        // 7 ── Bali Private Driver ───────────────────────────────────────────
        $u = User::factory()->create(['name' => 'Bali Private Driver', 'email' => 'transport-baliprivate@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Bali Private Driver',
            'type' => 'transport',
            'address' => 'Jl. Raya Seminyak No. 45',
            'city' => 'Badung',
            'province' => 'Bali',
            'country' => 'Indonesia',
            'phone' => '0813-45678901',
            'description' => 'Layanan sopir pribadi berpengalaman melayani rute Seminyak, Ubud, dan Kuta. Harga per 10 jam.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);

        $v1 = MerchantVehicle::create(['merchant_id' => $m->id, 'vehicle_type' => 'MPV', 'vehicle_name' => 'Toyota Avanza', 'total_units' => 5, 'price_per_day' => 450000]);
        $v2 = MerchantVehicle::create(['merchant_id' => $m->id, 'vehicle_type' => 'MPV', 'vehicle_name' => 'Toyota Kijang Innova', 'total_units' => 3, 'price_per_day' => 600000]);
        $this->seedVehicleAvailability($m, [$v1, $v2]);

        // 8 ── Pasar Sukawati ────────────────────────────────────────────────
        $u = User::factory()->create(['name' => 'Pasar Sukawati', 'email' => 'shop-pasarsukawati@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Pasar Sukawati',
            'type' => 'attraction',
            'address' => 'Jl. Raya Sukawati',
            'city' => 'Gianyar',
            'province' => 'Bali',
            'country' => 'Indonesia',
            'phone' => '0361-299222',
            'description' => 'Pasar seni tradisional terbesar di Bali, pusat oleh-oleh khas Bali: lukisan, patung, kain batik, dan kerajinan tangan.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);

        // ══════════════════════════════════════════════════════════════════════
        // YOGYAKARTA
        // ══════════════════════════════════════════════════════════════════════

        // 9 ── Malioboro Inn ─────────────────────────────────────────────────
        $u = User::factory()->create(['name' => 'Malioboro Inn', 'email' => 'hotel-malioboro@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Malioboro Inn',
            'type' => 'hotel',
            'address' => 'Jl. Malioboro No. 52',
            'city' => 'Yogyakarta',
            'province' => 'DI Yogyakarta',
            'country' => 'Indonesia',
            'phone' => '0274-512345',
            'description' => 'Hotel nyaman di jantung Malioboro, walking distance ke Tugu Yogya dan Pasar Beringharjo.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);

        $r1 = MerchantRoom::create(['merchant_id' => $m->id, 'room_type' => 'Standard', 'total_rooms' => 8, 'price_per_night' => 350000, 'description' => 'Standard room, 18m², AC, WiFi']);
        $r2 = MerchantRoom::create(['merchant_id' => $m->id, 'room_type' => 'Deluxe', 'total_rooms' => 4, 'price_per_night' => 550000, 'description' => 'Deluxe room, 24m², city view']);
        $this->seedRoomAvailability($m, [$r1, $r2]);

        // 10 ── Gudeg Yu Djum ────────────────────────────────────────────────
        $u = User::factory()->create(['name' => 'Gudeg Yu Djum', 'email' => 'resto-gudegyudjum@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Gudeg Yu Djum',
            'type' => 'restaurant',
            'address' => 'Jl. Wijilan',
            'city' => 'Yogyakarta',
            'province' => 'DI Yogyakarta',
            'country' => 'Indonesia',
            'phone' => '0274-515678',
            'description' => 'Gudeg legendaris khas Yogyakarta sejak 1950. Nikmati gudeg komplit dengan krecek, telur, dan ayam kampung.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);

        // 11 ── Candi Prambanan Tour ─────────────────────────────────────────
        $u = User::factory()->create(['name' => 'Candi Prambanan Tour', 'email' => 'tour-prambanan@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Candi Prambanan Tour',
            'type' => 'attraction',
            'address' => 'Jl. Raya Solo Km. 16, Klaten',
            'city' => 'Klaten',
            'province' => 'Jawa Tengah',
            'country' => 'Indonesia',
            'phone' => '0274-496401',
            'description' => 'Paket tour ke Candi Prambanan lengkap dengan tiket masuk dan guide lokal profesional berbahasa Indonesia/Inggris.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);

        // ══════════════════════════════════════════════════════════════════════
        // BANDUNG
        // ══════════════════════════════════════════════════════════════════════

        // 12 ── Dago Resort ──────────────────────────────────────────────────
        $u = User::factory()->create(['name' => 'Dago Resort', 'email' => 'hotel-dago@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Dago Resort',
            'type' => 'hotel',
            'address' => 'Jl. Dago Atas No. 120',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'country' => 'Indonesia',
            'phone' => '022-2504567',
            'description' => 'Resort di kawasan Dago dengan udara sejuk, pemandangan pegunungan, dan kolam renang air panas alami.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);

        $r1 = MerchantRoom::create(['merchant_id' => $m->id, 'room_type' => 'Standard', 'total_rooms' => 6, 'price_per_night' => 500000, 'description' => 'Standard room, 22m², garden view']);
        $r2 = MerchantRoom::create(['merchant_id' => $m->id, 'room_type' => 'Mountain View', 'total_rooms' => 3, 'price_per_night' => 800000, 'description' => 'Mountain view room, 30m², private balcony']);
        $this->seedRoomAvailability($m, [$r1, $r2]);

        // 13 ── Warung Lela ──────────────────────────────────────────────────
        $u = User::factory()->create(['name' => 'Warung Lela', 'email' => 'resto-lela@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Warung Lela',
            'type' => 'restaurant',
            'address' => 'Jl. Lembong',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'country' => 'Indonesia',
            'phone' => '022-4203456',
            'description' => 'Restoran masakan Sunda autentik dengan menu andalan nasi timbel, ayam goreng, dan sambal dadakan.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);

        // 14 ── Kawah Putih Tour ─────────────────────────────────────────────
        $u = User::factory()->create(['name' => 'Kawah Putih Tour', 'email' => 'tour-kawahputih@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Kawah Putih Tour',
            'type' => 'attraction',
            'address' => 'Ciwidey',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'country' => 'Indonesia',
            'phone' => '022-7890123',
            'description' => 'Paket wisata ke Kawah Putih Ciwidey termasuk tiket masuk dan transportasi lokal dari pusat Bandung.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);

        // ══════════════════════════════════════════════════════════════════════
        // LOMBOK
        // ══════════════════════════════════════════════════════════════════════

        // 15 ── Senggigi Beach Hotel ─────────────────────────────────────────
        $u = User::factory()->create(['name' => 'Senggigi Beach Hotel', 'email' => 'hotel-senggigi@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Senggigi Beach Hotel',
            'type' => 'hotel',
            'address' => 'Jl. Raya Senggigi',
            'city' => 'Lombok Barat',
            'province' => 'NTB',
            'country' => 'Indonesia',
            'phone' => '0370-693456',
            'description' => 'Hotel tepi pantai di kawasan Senggigi dengan sunset view dan akses langsung ke pasir putih.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);

        $r1 = MerchantRoom::create(['merchant_id' => $m->id, 'room_type' => 'Standard', 'total_rooms' => 7, 'price_per_night' => 600000, 'description' => 'Standard room, 22m², garden view']);
        $r2 = MerchantRoom::create(['merchant_id' => $m->id, 'room_type' => 'Beach Front', 'total_rooms' => 4, 'price_per_night' => 900000, 'description' => 'Beach front room, 30m², direct beach access, ocean view']);
        $this->seedRoomAvailability($m, [$r1, $r2]);

        // 16 ── Gili Trawangan Snorkeling ─────────────────────────────────────
        $u = User::factory()->create(['name' => 'Gili Trawangan Snorkeling', 'email' => 'tour-gilit@trusaba.id', 'role' => 'merchant']);
        $m = Merchant::create([
            'user_id' => $u->id,
            'name' => 'Gili Trawangan Snorkeling',
            'type' => 'attraction',
            'address' => 'Gili Trawangan',
            'city' => 'Lombok Utara',
            'province' => 'NTB',
            'country' => 'Indonesia',
            'phone' => '0819-12345678',
            'description' => 'Paket snorkeling di 3 titik sekitar Gili Trawangan: Turtle Point, Meno Wall, dan Air Bounty. Termasuk peralatan lengkap.',
            'is_active' => true,
            'wallet_balance' => 0,
        ]);
    }

    /**
     * Seed room availability for the next 90 days.
     *
     * @param  MerchantRoom[]  $rooms
     */
    protected function seedRoomAvailability(Merchant $merchant, array $rooms): void
    {
        $start = Carbon::now()->startOfDay();

        for ($d = 0; $d < 90; $d++) {
            $date = $start->copy()->addDays($d);

            foreach ($rooms as $room) {
                MerchantAvailability::create([
                    'merchant_id' => $merchant->id,
                    'resource_type' => 'room',
                    'resource_id' => $room->id,
                    'date' => $date,
                    'available_qty' => rand(1, $room->total_rooms),
                ]);
            }
        }
    }

    /**
     * Seed vehicle availability for the next 90 days.
     *
     * @param  MerchantVehicle[]  $vehicles
     */
    protected function seedVehicleAvailability(Merchant $merchant, array $vehicles): void
    {
        $start = Carbon::now()->startOfDay();

        for ($d = 0; $d < 90; $d++) {
            $date = $start->copy()->addDays($d);

            foreach ($vehicles as $vehicle) {
                MerchantAvailability::create([
                    'merchant_id' => $merchant->id,
                    'resource_type' => 'vehicle',
                    'resource_id' => $vehicle->id,
                    'date' => $date,
                    'available_qty' => rand(1, $vehicle->total_units),
                ]);
            }
        }
    }
}

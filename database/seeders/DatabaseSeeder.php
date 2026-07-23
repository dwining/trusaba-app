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
            'description' => '4-star hotel in central Seminyak, near Double Six Beach.',
            'profile_tags' => json_encode(['beach', 'surfing', 'luxury']),
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
                'description' => 'Booking #BOOK-001 - Deluxe Room 2 nights',
                'available_at' => now()->subDays(3),
            ]
        );
        WalletTransaction::firstOrCreate(
            ['merchant_id' => $m->id, 'type' => 'credit', 'amount' => 3500000],
            [
                'balance_after' => 5500000,
                'description' => 'Booking #BOOK-002 - Suite Room 3 nights',
                'available_at' => now()->subDay(),
            ]
        );
        WalletTransaction::firstOrCreate(
            ['merchant_id' => $m->id, 'type' => 'debit', 'amount' => 500000],
            [
                'balance_after' => 5000000,
                'description' => 'Withdrawal to Bank BCA',
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
            'description' => 'Peaceful resort in the heart of Ubud with beautiful rice field views.',
            'profile_tags' => json_encode(['nature', 'culture', 'wellness']),
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
            'description' => 'Iconic restaurant in Ubud with crispy fried duck and calming rice field views.',
            'profile_tags' => json_encode(['culinary', 'local', 'culture']),
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
            'description' => 'Legendary seafood eatery in Sanur, famous for fish soup and Balinese shrimp paste sambal.',
            'profile_tags' => json_encode(['culinary', 'beach', 'local']),
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
            'description' => 'Affordable hotel in central Kuta, just a 2-minute walk to Kuta Beach.',
            'profile_tags' => json_encode(['beach', 'budget', 'surfing']),
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
            'description' => 'Full-day tour package to Nusa Penida: Kelingking Beach, Broken Beach, Angel\'s Billabong. Includes round-trip fast boat and lunch.',
            'profile_tags' => json_encode(['beach', 'nature', 'photography']),
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
            'description' => 'Experienced private driver service covering Seminyak, Ubud, and Kuta routes. Priced per 10 hours.',
            'profile_tags' => json_encode(['transport', 'culture', 'nature']),
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
            'description' => 'Bali\'s largest traditional art market, a hub for Balinese souvenirs: paintings, sculptures, batik fabric, and handicrafts.',
            'profile_tags' => json_encode(['shopping', 'culture', 'local']),
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
            'description' => 'Comfortable hotel in the heart of Malioboro, walking distance to Tugu Yogya and Beringharjo Market.',
            'profile_tags' => json_encode(['culture', 'shopping', 'budget']),
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
            'description' => 'Legendary Yogyakarta gudeg since 1950. Enjoy complete gudeg with krecek, eggs, and free-range chicken.',
            'profile_tags' => json_encode(['culinary', 'local', 'culture']),
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
            'description' => 'Prambanan Temple tour package complete with entrance tickets and a professional local guide fluent in Indonesian/English.',
            'profile_tags' => json_encode(['culture', 'nature', 'photography']),
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
            'description' => 'Resort in the Dago area with cool air, mountain views, and a natural hot spring pool.',
            'profile_tags' => json_encode(['shopping', 'nature', 'budget']),
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
            'description' => 'Authentic Sundanese cuisine restaurant with signature dishes: nasi timbel, fried chicken, and freshly-made sambal.',
            'profile_tags' => json_encode(['culinary', 'local', 'culture']),
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
            'description' => 'Tour package to Kawah Putih Ciwidey including entrance tickets and local transportation from central Bandung.',
            'profile_tags' => json_encode(['nature', 'photography', 'shopping']),
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
            'description' => 'Beachfront hotel in the Senggigi area with sunset views and direct access to white sand.',
            'profile_tags' => json_encode(['beach', 'nature', 'luxury']),
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
            'description' => 'Snorkeling package at 3 spots around Gili Trawangan: Turtle Point, Meno Wall, and Air Bounty. Includes complete equipment.',
            'profile_tags' => json_encode(['beach', 'nature', 'adventure']),
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

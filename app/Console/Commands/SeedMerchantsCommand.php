<?php

namespace App\Console\Commands;

use App\Models\ChatRoom;
use App\Models\Merchant;
use App\Models\MerchantRoom;
use App\Models\MerchantVehicle;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedMerchantsCommand extends Command
{
    protected $signature = 'merchants:seed';

    protected $description = 'Seed merchants for 21 Indonesian cities with rooms and vehicles';

    public function handle()
    {
        $userId = User::first()?->id;
        if (! $userId) {
            $this->error('No users found in database.');

            return;
        }

        if (Merchant::count() > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            MerchantRoom::query()->delete();
            MerchantVehicle::query()->delete();
            Merchant::query()->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $cities = $this->cities();
        $total = 0;

        foreach ($cities as $data) {
            $count = $this->seedCity($data['city'], $data['province'], $userId);
            $total += $count;
            $this->info("  {$data['city']}: {$count} merchants");
        }

        $this->info("\nTotal: {$total} merchants across ".count($cities).' cities.');

        // Seed chat rooms for each city
        $this->info("\nSeeding chat rooms...");
        foreach ($cities as $data) {
            $slug = strtolower(str_replace(' ', '-', $data['city']));
            ChatRoom::firstOrCreate(
                ['slug' => $slug.'-travelers'],
                [
                    'name' => $data['city'].' Travelers',
                    'type' => 'group',
                    'destination' => $data['city'],
                ]
            );
        }
        $this->info('  21 chat rooms created.');
    }

    private function baseFields(string $city, string $province, int $userId): array
    {
        return [
            'user_id' => $userId,
            'city' => $city,
            'province' => $province,
            'country' => 'Indonesia',
            'phone' => $this->randomPhone(),
            'is_active' => true,
            'wallet_balance' => 0,
        ];
    }

    private function seedCity(string $city, string $province, int $userId): int
    {
        $count = 0;
        $names = $this->merchantNames();

        // Hotels (3-5)
        $hotelCount = rand(3, 5);
        $selectedHotels = $this->pick($names['hotels'], $hotelCount);
        foreach ($selectedHotels as $name) {
            $m = Merchant::create(array_merge($this->baseFields($city, $province, $userId), [
                'name' => $name,
                'type' => 'hotel',
                'address' => $this->randomAddress($city),
                'description' => $this->randomHotelDesc(),
                'profile_tags' => $this->randomTags(['hotel']),
            ]));

            $roomTypes = ['Standard', 'Superior', 'Deluxe', 'Suite', 'Family Room'];
            $roomCount = rand(2, 3);
            foreach ($this->pick($roomTypes, $roomCount) as $rt) {
                MerchantRoom::create([
                    'merchant_id' => $m->id,
                    'room_type' => $rt,
                    'description' => $this->randomRoomDesc($rt),
                    'price_per_night' => $this->hotelPrice(),
                    'total_rooms' => rand(3, 20),
                ]);
            }
            $count++;
        }

        // Restaurants (5-8)
        $restoCount = rand(5, 8);
        foreach ($this->pick($names['restaurants'], $restoCount) as $name) {
            Merchant::create(array_merge($this->baseFields($city, $province, $userId), [
                'name' => $name,
                'type' => 'restaurant',
                'address' => $this->randomAddress($city),
                'description' => $this->randomRestoDesc(),
                'profile_tags' => $this->randomTags(['food', 'restaurant']),
            ]));
            $count++;
        }

        // Attractions (3-5)
        $attrCount = rand(3, 5);
        foreach ($this->pick($names['attractions'], $attrCount) as $name) {
            Merchant::create(array_merge($this->baseFields($city, $province, $userId), [
                'name' => $name.' '.$city,
                'type' => 'attraction',
                'address' => $this->randomAddress($city),
                'description' => $this->randomAttrDesc(),
                'profile_tags' => $this->randomTags(['attraction', 'tourism']),
            ]));
            $count++;
        }

        // Transport (2-3)
        $transCount = rand(2, 3);
        foreach ($this->pick($names['transports'], $transCount) as $name) {
            $m = Merchant::create(array_merge($this->baseFields($city, $province, $userId), [
                'name' => $name.' '.$city,
                'type' => 'transport',
                'address' => $this->randomAddress($city),
                'description' => 'Transportation services in '.$city,
                'profile_tags' => $this->randomTags(['transport']),
            ]));

            $vehicles = [
                ['type' => 'Scooter', 'name' => 'Honda Scoopy', 'price' => rand(60000, 120000)],
                ['type' => 'Car', 'name' => 'Toyota Avanza', 'price' => rand(300000, 600000)],
                ['type' => 'Minibus', 'name' => 'Isuzu Elf', 'price' => rand(600000, 1200000)],
            ];
            foreach (array_slice($vehicles, 0, rand(1, 2)) as $vData) {
                MerchantVehicle::create([
                    'merchant_id' => $m->id,
                    'vehicle_type' => $vData['type'],
                    'vehicle_name' => $vData['name'],
                    'price_per_day' => $vData['price'],
                    'total_units' => rand(2, 10),
                ]);
            }
            $count++;
        }

        // Shopping (2-3) — uses 'other' type since enum lacks 'shopping'
        $shopCount = rand(2, 3);
        foreach ($this->pick($names['shopping'], $shopCount) as $name) {
            Merchant::create(array_merge($this->baseFields($city, $province, $userId), [
                'name' => $name.' '.$city,
                'type' => 'other',
                'address' => $this->randomAddress($city),
                'description' => 'Shopping destination in '.$city,
                'profile_tags' => $this->randomTags(['shopping']),
            ]));
            $count++;
        }

        return $count;
    }

    private function pick(array $pool, int $count): array
    {
        shuffle($pool);

        return array_slice($pool, 0, $count);
    }

    private function randomAddress(string $city): string
    {
        $streets = ['Jl. Raya', 'Jl. Malioboro', 'Jl. Sudirman', 'Jl. Diponegoro', 'Jl. Ahmad Yani',
            'Jl. Gatot Subroto', 'Jl. Merdeka', 'Jl. Pemuda', 'Jl. Thamrin', 'Jl. Veteran'];
        $areas = ['Downtown', 'City Center', 'Old Town', 'Waterfront', 'Hilltop', 'Beachfront',
            'Commercial District', 'Heritage Area', 'University Area', 'Business District'];

        return $this->pick($streets, 1)[0].' No. '.rand(1, 200).', '.$this->pick($areas, 1)[0].', '.$city;
    }

    private function randomPhone(): string
    {
        return '+62'.rand(800, 899).'-'.rand(1000, 9999).'-'.rand(1000, 9999);
    }

    private function randomTags(array $base): array
    {
        $extra = ['budget', 'affordable', 'popular', 'local-favorite', 'family-friendly',
            'instagrammable', 'halal', 'luxury', 'authentic', 'scenic'];

        return array_merge($base, $this->pick($extra, rand(2, 4)));
    }

    private function hotelPrice(): int
    {
        $prices = [150000, 200000, 250000, 300000, 350000, 400000, 450000, 500000,
            600000, 750000, 850000, 1000000, 1200000, 1500000];

        return $prices[array_rand($prices)];
    }

    private function randomHotelDesc(): string
    {
        $descs = [
            'Modern hotel with swimming pool, spa, and rooftop restaurant.',
            'Boutique hotel in the heart of the city with traditional architecture.',
            'Luxury resort with ocean views, infinity pool, and fine dining.',
            'Budget-friendly accommodation with clean rooms and free breakfast.',
            'Family-friendly hotel with kids pool, playground, and spacious rooms.',
            'Business hotel with conference rooms, high-speed WiFi, and 24h service.',
            'Heritage hotel in a restored colonial building with antique furnishings.',
            'Beachfront resort with private beach access and water sports facilities.',
            'Eco-friendly hotel surrounded by tropical gardens and rice fields.',
            'Urban hotel with sky bar, gym, and panoramic city views.',
        ];

        return $descs[array_rand($descs)];
    }

    private function randomRoomDesc(string $type): string
    {
        return match ($type) {
            'Standard' => 'Cozy room, 18m², AC, TV, private bathroom',
            'Superior' => 'Spacious room, 24m², king bed, balcony, minibar',
            'Deluxe' => 'Deluxe room, 32m², bathtub, pool view, premium amenities',
            'Suite' => 'Executive suite, 48m², living room, jacuzzi, butler service',
            'Family Room' => 'Family room, 36m², 2 queen beds, kids corner',
            default => 'Comfortable room with standard amenities',
        };
    }

    private function randomRestoDesc(): string
    {
        $descs = [
            'Authentic local cuisine in a traditional setting with live music.',
            'Modern fusion restaurant blending local flavors with international cuisine.',
            'Cozy cafe serving specialty coffee, pastries, and light meals.',
            'Seafood restaurant with fresh daily catch and ocean views.',
            'Rooftop dining with panoramic views and craft cocktails.',
            'Family-style restaurant serving generous portions of home-cooked meals.',
            'Fine dining establishment with tasting menus and wine pairing.',
            'Street food court with dozens of local food stalls.',
            'Vegetarian and vegan restaurant with organic farm-to-table concept.',
            'Steakhouse and grill with premium imported beef and local spices.',
        ];

        return $descs[array_rand($descs)];
    }

    private function randomAttrDesc(): string
    {
        $descs = [
            'Popular tourist destination with stunning views and photo spots.',
            'Historical landmark with guided tours and museum exhibits.',
            'Natural wonder with hiking trails, waterfalls, and wildlife.',
            'Cultural center showcasing traditional arts, dance, and crafts.',
            'Adventure park with zip lines, ATV trails, and camping grounds.',
            'Sacred temple complex with ancient architecture and spiritual significance.',
            'Marine park with snorkeling, diving, and coral reef exploration.',
            'Botanical garden with rare plant species and butterfly sanctuary.',
            'Theme park with rides, shows, and entertainment for all ages.',
            'Night market with local food, crafts, and live performances.',
        ];

        return $descs[array_rand($descs)];
    }

    private function cities(): array
    {
        return [
            ['city' => 'Jakarta', 'province' => 'DKI Jakarta'],
            ['city' => 'Bandung', 'province' => 'Jawa Barat'],
            ['city' => 'Surabaya', 'province' => 'Jawa Timur'],
            ['city' => 'Yogyakarta', 'province' => 'DI Yogyakarta'],
            ['city' => 'Bali', 'province' => 'Bali'],
            ['city' => 'Medan', 'province' => 'Sumatera Utara'],
            ['city' => 'Semarang', 'province' => 'Jawa Tengah'],
            ['city' => 'Makassar', 'province' => 'Sulawesi Selatan'],
            ['city' => 'Palembang', 'province' => 'Sumatera Selatan'],
            ['city' => 'Malang', 'province' => 'Jawa Timur'],
            ['city' => 'Solo', 'province' => 'Jawa Tengah'],
            ['city' => 'Bogor', 'province' => 'Jawa Barat'],
            ['city' => 'Padang', 'province' => 'Sumatera Barat'],
            ['city' => 'Pekanbaru', 'province' => 'Riau'],
            ['city' => 'Banjarmasin', 'province' => 'Kalimantan Selatan'],
            ['city' => 'Balikpapan', 'province' => 'Kalimantan Timur'],
            ['city' => 'Manado', 'province' => 'Sulawesi Utara'],
            ['city' => 'Mataram', 'province' => 'NTB'],
            ['city' => 'Batam', 'province' => 'Kepulauan Riau'],
            ['city' => 'Pontianak', 'province' => 'Kalimantan Barat'],
            ['city' => 'Ambon', 'province' => 'Maluku'],
        ];
    }

    private function merchantNames(): array
    {
        return [
            'hotels' => [
                'Grand Hyatt', 'JW Marriott', 'Hilton Garden Inn', 'Four Seasons',
                'Hotel Santika', 'Aston Hotel', 'Novotel', 'Ibis Styles',
                'Swiss-Belhotel', 'Mercure Hotel', 'Pullman Hotel', 'The Ritz-Carlton',
                'Harris Hotel', 'Grand Mercure', 'Best Western', 'Aryaduta Hotel',
                'Hotel Tentrem', 'Royal Ambarrukmo', 'Phoenix Hotel', 'Hotel Tugu',
                'Alila Hotel', 'Padma Resort', 'Ayana Resort', 'Kamaya Suites',
            ],
            'restaurants' => [
                'Bebek Goreng Pak Slamet', 'Sate Shinta', 'Gudeg Yu Djum',
                'Warung Sate Kambing', 'Rumah Makan Sederhana', 'Soto Ayam Lamongan',
                'Bakso President', 'Rawon Nguling', 'Pempek Candy', 'Rendang Sederhana',
                'Ikan Bakar Jimbaran', 'Coto Makassar', 'Ayam Betutu Gilimanuk',
                'Sop Buntut Bogor', 'Gado-Gado Bonbin', 'Martabak San Francisco',
                'Bubur Ayam Sukabumi', 'Nasi Goreng Kambing Kebon Sirih',
                'Sate Padang Ajo', 'Mie Ayam Tumini', 'The Eatery', 'Sky Dining Rooftop',
            ],
            'attractions' => [
                'Grand Mosque of', 'Historic Palace of', 'Botanical Garden', 'Waterfall',
                'Mountain View Point', 'Beach Club', 'National Park', 'Art Museum',
                'Cultural Village', 'Sunset Point', 'Monkey Forest', 'Rice Terrace',
                'Hot Springs', 'Heritage Street', 'Night Safari', 'Bird Park',
                'Water Park', 'Surf Beach', 'City Square', 'Floating Market',
            ],
            'transports' => [
                'Blue Bird Rent', 'Happy Travel', 'City Trans', 'Fast Go',
                'Tour Van', 'Easy Ride', 'Rent Car', 'Go Trans',
            ],
            'shopping' => [
                'City Mall', 'Grand Plaza', 'Souvenir Center', 'Artisan Market',
                'Factory Outlet', 'Flea Market', 'Night Market', 'Craft Bazaar',
            ],
        ];
    }
}

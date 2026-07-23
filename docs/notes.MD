# TruSaba — Project Summary

## Overview
TruSaba is a Laravel 13 travel booking app. Users create AI-generated itineraries, browse matched merchants (hotels, attractions, restaurants, transport, shopping), add items to cart, and checkout.

## Tech Stack
- **PHP** 8.5, **Laravel** 13, **Filament** v5
- **Livewire** v4, **Tailwind CSS** v4
- **Queue**: database driver (async job processing)
- **Payment**: Midtrans (mock MVP)
- **AI**: OpenCode API (with mock fallback for MVP demo)

## Architecture
- `GenerateItineraryJob` — AI/mock itinerary generation + merchant matching
- `ItineraryController` — CRUD + show with coverage tracking
- `BookingController` — Merchant selection + booking form
- `CartController` — Cart add/remove/checkout (→ Transaction + Bookings)
- `BKxController` — ???

## Session Changes — Fase 1 (Strict Matching + Booking Flow Fixes)

### 1. GenerateItineraryJob — Strict Merchant Matching
**File:** `app/Jobs/GenerateItineraryJob.php`
- `similar_text` threshold: **70% → 95%** (prevents false matches like "Hotel Bali Indah" → "Seminyak Palm Hotel")
- `substring` overlap: **7 → 12 chars**
- No-match items: keep `is_bookable=true` (was setting `false` before)
- Added guard clause for empty `$availableMerchants`
- **Queue:** keep `implements ShouldQueue` — restart worker after code changes: `php artisan queue:restart`

### 2. `ItineraryController + show.blade.php` — Three-Mode Item Display
**Files:** `app/Http/Controllers/ItineraryController.php`, `resources/views/itinerary/show.blade.php`

Three modes per itinerary item:
| Mode | Condition | Display |
|------|-----------|---------|
| 1 | `merchant_id` present (strict match) | Real merchant name + "Add to Cart" button |
| 2 | `is_bookable` + no `merchant_id` (unmatched) | Generic label ("Hotel") + "Mulai Rp X" (lowest DB price) + "Pilih Hotel →" |
| 3 | `!is_bookable` | "Info only" badge |

- Lowest-price queries from `MerchantRoom`/`MerchantVehicle` filtered by destination city
- Indonesian type labels (`typeDisplay`)
- `estimated_cost` **hidden** for Mode 2 items (shows lowest DB price instead)

### 3. BookingController + detail.blade.php — Merchant Selection Step
**Files:** `app/Http/Controllers/BookingController.php`, `resources/views/booking/detail.blade.php`

Two modes in `create()`:
- **`@if(isset($availableMerchants))`** — No merchant selected yet: heading + merchant list cards with prices, "Pilih" button → JS redirect to booking form with `merchant_id`
- **`@else`** — Merchant selected: existing adaptive booking form (hotel/restaurant/attraction/transport/shopping)

Changes:
- Removed "AI Recommend" badge + "Estimasi AI" from merchant selection page
- Removed itinerary item info card from booking form when `merchant_id` is null (user-selected, not matched)
- **`$itinerary`** variable loaded directly from `itinerary_id` when no `item_id` present
- **Destination filter:** loads itinerary directly to get `destination` → `cityHint` for merchant query when no `item_id`

### 4. Date Handling — Priority Chain
**File:** `app/Http/Controllers/BookingController.php`

Three-branch date priority:
1. `check_in`/`check_out` query params (from coverage gap buttons)
2. `itineraryItem?->itinerary` dates (from matched item)
3. `$itinerary` dates directly from `itinerary_id` (additional hotel, no item)
4. `now()+7` fallback (no itinerary at all)

`$defaultCheckOut` was hardcoded `now()+9` — **fixed** to use itinerary `end_date`.

### 5. CartController — Additional Hotel Support
**File:** `app/Http/Controllers/CartController.php`

- `itinerary_item_id` already `nullable` in validation
- **Dedup for null `itinerary_item_id`:** `updateOrCreate` with composite key `(user_id, merchant_id, check_in_date, itinerary_id, itinerary_item_id=NULL)`
- **Overlap guard:** rejects hotel cart additions that overlap with existing bookings or cart items (date range collision check)
- Error message in **English**

### 6. Price Fixes — Booking Form
**File:** `resources/views/booking/detail.blade.php`

- Initial `pricePerUnit` was hardcoded `1.100.000` → now dynamic from merchant's first room
- `updateTotal()` JS now updates `pricePerUnit` on room type change
- `room_type` hidden field dynamic from DB (was hardcoded `"Superior Twin"`)

### 7. Hero Section — Itinerary Context
**File:** `resources/views/booking/detail.blade.php`

Hero now shows:
```
Trip to Bali · 01 Aug–08 Aug 2026    ← 15px bold (itinerary title + dates)
BALI · HOTEL                         ← 12px uppercase (province + type)
```
- Uses `province` instead of `city` for broader location context
- Flex layout fixed with `flex-direction:column` + `justify-content:flex-end`

### 8. Hotel Coverage Tracking — Split Stay
**Files:** `app/Http/Controllers/ItineraryController.php`, `resources/views/itinerary/show.blade.php`

Night-by-night coverage calculation:
- Queries both `Booking` (confirmed) AND `CartItem` (pending cart) records
- Marks each night covered/uncovered
- Groups uncovered nights into gap ranges
- Coverage bar: color-coded visual (accent=covered, border=uncovered)
- "Tambah Hotel" buttons per gap with pre-filled `check_in`/`check_out` params
- "✓ Semua malam sudah ter-cover!" when 0 gaps

### 9. Back Button Fixes
**Files:** `resources/views/booking/detail.blade.php`, `resources/views/cart/index.blade.php`

Replaced all `url()->previous()` with proper named routes:
| Location | Before | After |
|----------|--------|-------|
| Booking hero (x2) | `url()->previous()` | `route('itineraries.show')` with fallback |
| Empty merchant fallback | `url()->previous()` + "Kembali" | `route('itineraries.show')` with fallback + "Back" |
| Shopping back | `url()->previous()` | `route('itineraries.show')` (always) |
| Cart back | `url()->previous()` | `route('itineraries.index')` |

### 10. Cache & Queue Pattern
After code changes to queued jobs: `php artisan optimize:clear` → `php artisan queue:restart`

---

## Flow Summary

```
/onboarding → generate itinerary → GenerateItineraryJob (queue)
    │
    ▼
/itineraries/{id}  ←── coverage bar + 3-mode items
    │
    ├── Matched item → [Add to Cart] → booking form → cart
    ├── Unmatched item → [Pilih Hotel →] → merchant selection → booking form → cart
    └── Coverage gap → [Tambah Hotel] → merchant selection → booking form → cart
                                                                    │
    ┌───────────────────────────────────────────────────────────────┘
    ▼
/cart → [Checkout] → 1 Transaction + N Bookings
```

## Known Gaps (Future Phases)
- **Fase 2:** Prices from DB for all types (currently only hotel/transport)
- **Fase 3:** Itinerary editor (toggle edit mode, delete/add items)
- `itinerary_items.schedule_time` uses `TIME` type but stores date (bug in mock data)
- AI API integration (currently mock-only)

## Files Modified
| File | Changes |
|------|---------|
| `app/Jobs/GenerateItineraryJob.php` | Strict matching thresholds, guard clause |
| `app/Http/Controllers/ItineraryController.php` | Lowest prices, coverage tracking, cart in coverage |
| `app/Http/Controllers/BookingController.php` | Merchant selection mode, date priority, destination filter |
| `app/Http/Controllers/CartController.php` | Null item_id dedup, overlap guard |
| `resources/views/itinerary/show.blade.php` | 3-mode cards, coverage section |
| `resources/views/booking/detail.blade.php` | Merchant selection UI, hero, prices, item card hiding, back buttons |
| `resources/views/cart/index.blade.php` | Back button fix |

---

## Fase 2 — Itinerary Editor + Budget Overview + Full English Translation

### 1. Itinerary Editor — Backend
**Files new:** `app/Http/Controllers/ItineraryItemController.php`, migration (lat/lng), 3 routes
**Files modified:** `app/Models/ItineraryItem.php`, `config/services.php`, `.env`

- **Migration:** `latitude`/`longitude` (DECIMAL 10,7) nullable on `itinerary_items`
- **Config:** `GOOGLE_MAPS_API_KEY` in `.env` + `config/services.php`
- **Model:** `latitude`, `longitude` added to fillable
- **Routes:** `POST itinerary-items.store`, `PUT itinerary-items.update`, `DELETE itinerary-items.destroy`
- **Controller:** `ItineraryItemController` with guards:
  - `store()`: auto `sort_order`, `is_bookable=false` for user-added items
  - `update()`: blocks past items, type-lock for matched items, `estimated_cost` null→0 fix
  - `destroy()`: blocks items with active bookings or past schedule time
  - **`schedule_time` fix:** `substr((string) $item->schedule_time, -8)` everywhere — Carbon datetime cast was producing double-date strings

### 2. Itinerary Editor — UI
**File:** `resources/views/itinerary/show.blade.php`

- **Edit/delete buttons:** Always visible on every item (removed `display:none` inline + CSS toggle)
- **"+ Add Activity" per day:** Button at bottom of each day section
- **Modal form** (shared add/edit):
  - Category dropdown (Hotel, Restaurant, Attraction, Transport, Shopping, Other)
  - Name, Time, Description, Est. Cost, Location fields
  - Form auto-switches between POST (add) and PUT (edit) based on context
- **Google Maps picker modal:**
  - Embedded Google Map with search box (Places Autocomplete)
  - Click-to-place + drag-to-move marker
  - Geocoding fallback on Enter key (handles when Autocomplete dropdown doesn't appear)
  - Stores `location` text + `latitude`/`longitude` coordinates
  - Error message when map fails to load
- **Location display:** In view mode — `📍` link to `https://www.google.com/maps?q=lat,lng`
- **Removed:** "Edit" toggle button (useless after always-visible fix), "Edit Itinerary" button (`history.back()`)

### 3. Item Display Fixes
**File:** `resources/views/itinerary/show.blade.php`

- **Title:** `$item->name ?: typeDisplay` (was gated by `merchant_id` — user-added items show custom names)
- **Description:** Custom text if exists; "Choose... in..." only for bookable unmatched items (Mode 2)
- **Hotels in timeline:** No price, no Add to Cart — hotel booking exclusively via coverage section (`$item->type !== 'hotel'` gate)
- **Prices:** Hidden for all hotel items in timeline
- **Sort order:** Items sorted by `day_number` → `schedule_time` → `sort_order`

### 4. Budget Overview
**Files:** `app/Http/Controllers/ItineraryController.php`, `resources/views/itinerary/show.blade.php`

New card below cart section:
```
Budget Overview
─────────────────────────────
AI Estimate         Rp 4.500.000
Paid                Rp 1.350.000   (from confirmed bookings — persists after checkout)
In Cart (2 items)   Rp 2.200.000   (current cart — cleared after checkout)
Other Activities    Rp 1.800.000   (info-only items sum)
─────────────────────────────
Estimated Total     Rp 5.350.000   (paid + cart + info-only)
```

- `$bookedTotal`: sum of confirmed/pending/checked_in/completed bookings
- `$infoOnlyTotal`: sum of non-bookable itinerary items
- Card only shows when at least one value is non-zero

### 5. Bug Fixes & Quality
- **Transaction `booking_id`:** Migration made nullable — cart checkout was 500 error
- **Itinerary delete:** Button visible for all non-cancelled statuses (was only draft)
- **Coverage tracking:** Includes cart items (not just confirmed bookings)
- **Budget card:** Margin adjusted (`margin-top:4px`)
- **Regular cache:** `php artisan optimize:clear` after every code change

### 6. Full English Translation
All Indonesian text replaced with English:

| Before | After |
|--------|-------|
| 🏨 Penginapan | 🏨 Accommodation |
| Restoran | Restaurant |
| Tiket Wisata | Attraction |
| Transportasi | Transport |
| Aktivitas | Activity |
| Pilih ... di ... | Choose ... in ... |
| Pilih ... → | Choose ... → |
| Lain-lain | Other |
| Tambah Hotel · ... malam → | Add Hotel · ... nights → |
| malam ter-booking | nights booked |
| malam | nights |
| Semua malam sudah ter-cover! | All nights covered! |
| Rentang tanggal tumpang tindih... | Date range overlaps... |

### 7. Cleanup
- Removed useless "Edit" toggle button + `toggleEditMode()` JS function
- Removed "Edit Itinerary" button (`history.back()` — did nothing useful)

### 8. Known Issues
- **Google Maps:** Legacy Places Autocomplete may not work on new GCP projects. User must enable `Places API` (not just Places API (New)) in Google Cloud Console. API key must have billing enabled + no HTTP referrer restrictions for localhost.
- **`schedule_time` cast:** MySQL TIME column + model `datetime` cast = Carbon fills today's date. Pattern used everywhere: `substr((string) $item->schedule_time, -8)` to extract time-only portion.

## Files Modified (Fase 2)
| File | Changes |
|------|---------|
| `app/Http/Controllers/ItineraryItemController.php` | **New** — full CRUD with guards |
| `app/Http/Controllers/ItineraryController.php` | infoOnlyTotal, bookedTotal, schedule_time sort, coverage includes cart |
| `routes/web.php` | +3 itinerary-item routes |
| `config/services.php` | google_maps.api_key |
| `.env` | GOOGLE_MAPS_API_KEY |
| `app/Models/ItineraryItem.php` | latitude, longitude fillable |
| `database/migrations/*_add_lat_lng_to_itinerary_items.php` | **New** |
| `database/migrations/*_make_booking_id_nullable_on_transactions.php` | **New** |
| `resources/views/itinerary/show.blade.php` | Editor UI, modals, map, budget overview, translations, cleanup, copy itinerary |
| `docs/notes.MD` | This file |
| `app/Console/Commands/SeedMerchantsCommand.php` | **New** — 414 merchants across 21 cities |

---

## Fase 3 — Data & Quality

### 1. Copy Itinerary Feature
**File:** `resources/views/itinerary/show.blade.php`

"📋 Copy Itinerary" button above "Confirm & Save". Copies a flat, WhatsApp-friendly text:
```
Trip to Yogyakarta
01 Aug – 04 Aug 2026 · 4 days

── Day 1 · Fri, 01 Aug ──
08:00  Malioboro Inn (Hotel) — Check-in hotel
📍 Jl. Malioboro No. 52
09:00  Gudeg Yu Djum (Restaurant)
...
```
- Text built server-side from loaded data (no DOM scraping)
- `navigator.clipboard.writeText()` with `document.execCommand('copy')` fallback
- `trim()` to remove trailing whitespace from Blade template
- Flat layout, no indentation — designed for WhatsApp sharing

### 2. Merchant Seeder — 21 Indonesian Cities
**File:** `app/Console/Commands/SeedMerchantsCommand.php`

Custom Artisan command `php artisan merchants:seed` generates realistic merchants:

| Type | Per City | Total (21 cities) |
|------|----------|-------------------|
| Hotels | 3-5 (each with 2-3 rooms + prices) | ~80 hotels, ~200 rooms |
| Restaurants | 5-8 | ~130 |
| Attractions | 3-5 | ~80 |
| Transport | 2-3 (each with 1-2 vehicles + prices) | ~50, ~75 vehicles |
| Shopping | 2-3 (stored as `other` type) | ~50 |
| **Total** | **16-23 per city** | **414 merchants** |

Cities: Jakarta, Bandung, Surabaya, Yogyakarta, Bali, Medan, Semarang, Makassar, Palembang, Malang, Solo, Bogor, Padang, Pekanbaru, Banjarmasin, Balikpapan, Manado, Mataram, Batam, Pontianak, Ambon.

Schema notes (discovered during seeding):
- `merchants` required fields: `user_id`, `country`, `wallet_balance`
- `merchant_rooms` required: `total_rooms` (not `capacity`/`available_qty`)
- `merchant_vehicles` required: `vehicle_name`, `total_units` (not `description`/`capacity`)
- `type` enum: `hotel,restaurant,cafe,attraction,transport,other` — no `shopping`, used `other`

### 3. Mock Data English Fixes
**File:** `app/Jobs/GenerateItineraryJob.php`

| Before | After |
|--------|-------|
| `'Sarapan Hotel'` | `'Hotel Breakfast'` |
| `"Hotel {$dest} Indah"` | `"{$dest} Hotel"` |

AI prompt already enforces English: "Use English for all descriptions (name, description, tips, theme, summary)".

### 4. Booking Form — Participants Pre-fill
**File:** `resources/views/booking/detail.blade.php`

Attraction tickets and restaurant "Number of people" fields default to itinerary's `total_participants`:
```blade
value="{{ $itineraryItem?->itinerary?->total_participants ?? 2 }}"
```

### 5. Data Cleanup
- All itineraries, bookings, cart items, transactions deleted for fresh start
- `chat_messages` table cleared

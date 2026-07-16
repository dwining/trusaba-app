# TruSaba — Progress Tracker

> Checklist progres pembangunan MVP TruSaba. Centang `[x]` setelah selesai.

---

## Fase 0 — Setup Project

### 0.1 — Environment & Project
- [x] Buat database `trusaba_db` di MySQL
- [x] Install Laravel 13 project via Composer
- [x] Konfigurasi `.env` (DB, OpenCode endpoint, app URL)
- [x] Generate application key

### 0.2 — Dependencies
- [x] Install `laravel/sanctum`
- [x] Install `laravel/socialite`
- [x] Install `filament/filament` v5
- [x] Setup Tailwind CSS (Laravel Vite)
- [x] Setup `Plus Jakarta Sans` font

### 0.3 — Database Migrations
- [x] `users` (role ENUM, google_id, avatar, is_active)
- [x] `traveller_profiles` (birth_date, hobbies, interests, default_budget)
- [x] `merchants` (type, address, wallet_balance, logo)
- [x] `merchant_rooms` (room_type, total_rooms, price_per_night)
- [x] `merchant_vehicles` (vehicle_type, total_units, price_per_day)
- [x] `merchant_availability` (resource_type, date, available_qty)
- [x] `itineraries` (destination, start_date, end_date, budget, status, ai_raw_response)
- [x] `itinerary_items` (day_number, schedule_time, type, name, estimated_cost, is_bookable)
- [x] `bookings` (booking_type, check_in/out, amount, status, voucher_code)
- [x] `transactions` (payment_method, payment_gateway, gateway_trx_id, status)
- [x] `wallet_transactions` (type, amount, balance_after, available_at)
- [x] `withdrawal_requests` (bank_name, account_number, status, processed_at)
- [x] `reminders` (remind_at, type, message, is_sent)
- [x] `expense_uploads` (file_path, amount, description, is_processed)
- [x] `admin_assignments` (manager_id, officer_id)
- [x] `officer_merchants` (officer_id, merchant_id)
- [x] `sos_logs` (latitude, longitude, message, status, handled_by)
- [x] Run `php artisan migrate`

### 0.4 — Eloquent Models + Relations
- [x] `User` model (with role cast, traveller profile relation)
- [x] `TravellerProfile` model
- [x] `Merchant` model (+ rooms, vehicles, availability relations)
- [x] `MerchantRoom` model
- [x] `MerchantVehicle` model
- [x] `MerchantAvailability` model
- [x] `Itinerary` model (+ items, bookings relations)
- [x] `ItineraryItem` model
- [x] `Booking` model (+ transaction relation)
- [x] `Transaction` model
- [x] `WalletTransaction` model
- [x] `WithdrawalRequest` model
- [x] `Reminder` model
- [x] `ExpenseUpload` model
- [x] `AdminAssignment` model
- [x] `OfficerMerchant` model
- [x] `SosLog` model

### 0.5 — Filament Panels
- [x] Admin panel (`/admin`) — role gate: `officer`, `manager`, `superadmin`
- [ ] Merchant panel (`/merchant`) — role gate: `merchant`

### 0.6 — Design Token Integration
- [x] Extract tokens dari `design/trusaba.css` → Tailwind config
- [x] Copy `logo.jpeg` ke `public/`
- [x] Setup font `Plus Jakarta Sans` via Google Fonts / self-hosted
- [x] Verify warna: Primary `#066fda`, Secondary `#fcc415`

### 0.7 — OpenCode Client Service
- [x] `app/Services/OpenCodeClient.php` — POST ke `http://0.0.0.0:4096`
- [x] Config `opencode.php` (endpoint, timeout, retry)
- [x] Test koneksi: ping OpenCode Server

### 0.8 — PWA Shell (Base Layout)
- [x] `resources/views/layouts/app.blade.php` (phone frame + safe area)
- [x] Bottom nav component
- [x] App header component
- [x] Status bar component

---

## Fase 1 — Auth & Profiling

### 1.1 — Registrasi & Login
- [ ] Halaman Splash (`/` → redirect ke auth setelah 2.6 detik)
- [ ] Halaman Auth (`/auth`) — Login + Register tab, Google OAuth button
- [ ] Backend: Register (email + password)
- [ ] Backend: Login (email + password → Sanctum token)
- [ ] Backend: Google OAuth redirect + callback (Socialite)
- [ ] Backend: Logout
- [ ] Middleware role-based access (`auth`, `role:traveller`)

### 1.2 — Onboarding / Profiling (4-step wizard)
- [ ] Step 1: Destinasi + Tanggal lahir (mandatory validasi)
- [ ] Step 2: Hobi (chip multi-select)
- [ ] Step 3: Minat (chip multi-select)
- [ ] Step 4: Budget + Tanggal perjalanan + Jumlah peserta
- [ ] Backend: `PUT /profile` — simpan traveller profile
- [ ] Redirect ke loading screen setelah submit

---

## Fase 2 — AI Itinerary

### 2.1 — Loading Screen
- [ ] Halaman Loading AI (`/itinerary/loading`) — animasi orbit + progress bar
- [ ] Polling status itinerary (`GET /itineraries/{id}/status`)
- [ ] Redirect ke itinerary setelah selesai

### 2.2 — GenerateItineraryJob
- [ ] `app/Jobs/GenerateItineraryJob.php` — dispatch ke queue
- [ ] Build prompt dari data profil + destination
- [ ] POST ke OpenCode Server → parse JSON response
- [ ] Simpan ke `itineraries` + `itinerary_items`
- [ ] Error handling: retry 3x, backoff 30/60/120 detik
- [ ] Status handling: `processing` → `draft` / `failed`

### 2.3 — Itinerary Display
- [ ] Halaman Itinerary (`/itineraries/{id}`) — timeline per hari
- [ ] Budget summary card + bar chart
- [ ] Day sections dengan timeline activity items
- [ ] Booking button pada item yang `is_bookable`
- [ ] Edit itinerary (manual adjustment)
- [ ] Konfirmasi & simpan itinerary

### 2.4 — Merchant Matching
- [ ] Background job: match itinerary items dengan merchant (by type, city, availability)
- [ ] Update `itinerary_items.merchant_id`

---

## Fase 3 — Booking & Payment

### 3.1 — Hotel Detail
- [ ] Halaman Detail Hotel (`/hotels/{id}`) — hero image, info, room options
- [ ] Backend: `GET /merchants/{id}/availability`
- [ ] Pilih kamar + tanggal check-in/check-out

### 3.2 — Payment
- [ ] Halaman Pembayaran (`/payment`) — ringkasan + metode bayar
- [ ] Backend: `POST /bookings` — buat order booking
- [ ] Integrasi Midtrans (Snap / Core API)
- [ ] Webhook handler: `POST /transactions/callback`
- [ ] Update status booking + kirim notifikasi

### 3.3 — Booking Success / Voucher
- [ ] Halaman Success (`/booking/{id}/success`) — voucher digital
- [ ] QR code placeholder + kode booking
- [ ] Tombol: Buka Dashboard Hari Ini / Kembali ke Itinerary

---

## Fase 4 — On-Trip Companion

### 4.1 — Dashboard Hari Ini
- [ ] Halaman Today (`/today`) — jadwal harian + timeline live
- [ ] Status aktivitas: done, next, upcoming
- [ ] Voucher aktif (quick access)
- [ ] Greeting card (hari, kota, cuaca)

### 4.2 — SOS
- [ ] FAB SOS button (floating, danger red)
- [ ] Konfirmasi modal: "Kirim sinyal darurat?"
- [ ] Backend: `POST /sos` — kirim lokasi + alert ke admin
- [ ] SOS Sent modal: konfirmasi terkirim + link ke chat CS

### 4.3 — Chat AI Customer Service
- [ ] Halaman Chat (`/chat`) — bubble AI & user
- [ ] Backend: `POST /chat` — stream response dari OpenCode
- [ ] Context: profil traveller + itinerary aktif
- [ ] Chat input bar + send button

### 4.4 — History
- [ ] Halaman History (`/history`) — tab Riwayat Traveling + Riwayat Transaksi
- [ ] List itinerary selesai/berjalan
- [ ] List transaksi per trip

### 4.5 — Upload Bukti Transaksi
- [ ] Halaman Upload (`/expenses/upload`) — foto, nominal, kategori, catatan
- [ ] Backend: `POST /expense-uploads`
- [ ] Validasi: file JPG/PNG/PDF max 5MB

---

## Fase 5 — Admin Dashboard (Filament)

### 5.1 — Admin Panel Setup
- [ ] Panel `/admin` dengan Filament v5
- [ ] Auth guard: `web` + role check

### 5.2 — Admin Resources
- [ ] Traveller Resource (list, detail, suspend)
- [ ] Merchant Resource (CRUD, activation)
- [ ] Officer Resource (CRUD oleh Manager/Superadmin)
- [ ] Manager Resource (CRUD oleh Superadmin)
- [ ] Itinerary Resource (view all)
- [ ] Transaction Resource (monitor, audit)
- [ ] SOS Logs Resource (monitor, handle)

### 5.3 — Admin Pages
- [ ] Dashboard overview (stats: travellers, merchants, transactions, itineraries)
- [ ] App Configuration page (Superadmin only)
- [ ] AI Agent Configuration page (Superadmin only)
- [ ] Payment Gateway Configuration page (Superadmin only)

---

## Fase 6 — Merchant Dashboard (Filament)

### 6.1 — Merchant Panel Setup
- [ ] Panel `/merchant` dengan Filament v5
- [ ] Auth guard: `web` + role check (`merchant`)

### 6.2 — Merchant Resources
- [ ] Booking Resource (list incoming, confirm/reject, checkin, complete)
- [ ] Room Resource (CRUD room types, pricing)
- [ ] Vehicle Resource (CRUD vehicle types, pricing)
- [ ] Availability Resource (manage date-based inventory)

### 6.3 — Merchant Pages
- [ ] Wallet Dashboard (saldo, history)
- [ ] Withdrawal Request (form + history)
- [ ] Transaction Report (filter by date, type)
- [ ] Profile & Settings

---

## Fase 7 — API (REST v1)

### 7.1 — Auth Endpoints
- [ ] `POST /api/v1/auth/register`
- [ ] `POST /api/v1/auth/login`
- [ ] `GET /api/v1/auth/google/redirect`
- [ ] `GET /api/v1/auth/google/callback`
- [ ] `POST /api/v1/auth/logout`

### 7.2 — Profile Endpoints
- [ ] `GET /api/v1/profile`
- [ ] `PUT /api/v1/profile`

### 7.3 — Itinerary Endpoints
- [ ] `POST /api/v1/itineraries/generate`
- [ ] `GET /api/v1/itineraries/{id}/status`
- [ ] `GET /api/v1/itineraries`
- [ ] `GET /api/v1/itineraries/{id}`
- [ ] `PUT /api/v1/itineraries/{id}`
- [ ] `DELETE /api/v1/itineraries/{id}`

### 7.4 — Booking Endpoints
- [ ] `POST /api/v1/bookings`
- [ ] `GET /api/v1/bookings`
- [ ] `GET /api/v1/bookings/{id}`

### 7.5 — Availability Endpoints
- [ ] `GET /api/v1/merchants/{id}/availability`

### 7.6 — Transaction Endpoints
- [ ] `POST /api/v1/transactions/callback`
- [ ] `GET /api/v1/transactions`

### 7.7 — Expense Upload Endpoints
- [ ] `POST /api/v1/expense-uploads`

### 7.8 — Notification Endpoints
- [ ] `GET /api/v1/notifications`
- [ ] `PUT /api/v1/notifications/{id}/read`
- [ ] `PUT /api/v1/notifications/read-all`

### 7.9 — SOS Endpoints
- [ ] `POST /api/v1/sos`

### 7.10 — Merchant API Endpoints
- [ ] `GET /api/v1/merchant/bookings`
- [ ] `PUT /api/v1/merchant/bookings/{id}/confirm`
- [ ] `PUT /api/v1/merchant/bookings/{id}/checkin`
- [ ] `PUT /api/v1/merchant/bookings/{id}/complete`
- [ ] `GET /api/v1/merchant/wallet`
- [ ] `POST /api/v1/merchant/withdrawals`
- [ ] `GET /api/v1/merchant/rooms`
- [ ] `POST /api/v1/merchant/rooms`
- [ ] `PUT /api/v1/merchant/rooms/{id}`
- [ ] `GET /api/v1/merchant/vehicles`
- [ ] `POST /api/v1/merchant/vehicles`

---

## Fase 8 — Polish & Finalization

### 8.1 — PWA
- [ ] Service worker (offline cache)
- [ ] Web manifest (`manifest.json`)
- [ ] Install prompt
- [ ] Push notification (Web Push API)

### 8.2 — Email & Notification
- [ ] Email reminder (H-3, H-1 sebelum trip)
- [ ] Email konfirmasi booking
- [ ] In-app notification via dashboard

### 8.3 — Responsive Validation
- [ ] Test at 360×800 (mobile compact)
- [ ] Test at 390×844 (mobile standard)
- [ ] Test at 430×932 (mobile large)
- [ ] Test at 820×1180 (tablet portrait)
- [ ] Test at 1440×900 (desktop)
- [ ] No horizontal overflow on all viewports

### 8.4 — Security
- [ ] HTTPS enforced
- [ ] CSRF protection
- [ ] Rate limiting (AI + payment endpoints)
- [ ] File upload validation (MIME, size)
- [ ] Google OAuth token validation

---

## Ringkasan Progress

| Fase | Status | Selesai |
|------|--------|---------|
| 0 — Setup Project | ✅ Selesai | 7/8 |
| 1 — Auth & Profiling | ⬜ Pending | 0/2 |
| 2 — AI Itinerary | ⬜ Pending | 0/4 |
| 3 — Booking & Payment | ⬜ Pending | 0/3 |
| 4 — On-Trip Companion | ⬜ Pending | 0/5 |
| 5 — Admin Dashboard | ⬜ Pending | 0/3 |
| 6 — Merchant Dashboard | ⬜ Pending | 0/3 |
| 7 — API (REST v1) | ⬜ Pending | 0/10 |
| 8 — Polish & Finalization | ⬜ Pending | 0/4 |

---

## Catatan Teknis

| Item | Value |
|------|-------|
| Database | MySQL `trusaba_db` · user `root` · pass `BaksoBulat99` |
| OpenCode Server | `http://0.0.0.0:4096` |
| Warna Primary | `#066fda` (OKLch: 0.552, 0.184, 255.7) |
| Warna Secondary | `#fcc415` (OKLch: 0.847, 0.170, 87.3) |
| Font | Plus Jakarta Sans |
| Bahasa UI | Indonesia (MVP) |
| Target Platform | PWA (mobile-first, ~390px phone frame) |

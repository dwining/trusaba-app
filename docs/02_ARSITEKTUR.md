# Arsitektur Teknis — TruSaba

## 1. Stack Teknologi

| Layer | Teknologi |
|-------|-----------|
| Frontend | Tailwind CSS (PWA) |
| Backend | PHP + Laravel 13 |
| Dashboard Admin & Merchant | Laravel Filament V5 |
| Database | MySQL |
| AI Agent | OpenCode Server |
| Auth | Laravel Auth + Google OAuth (Socialite) |
| Queue | Laravel Queue (database/Redis driver) |
| Email | Laravel Mail (SMTP / Mailgun) |
| Push Notifikasi | Laravel Notifications + Web Push |
| Payment | Midtrans (MVP) |

### Warna Brand
```
Primary   : #066fda
Secondary : #fcc415
Accent    : #fcc415
Background: #ffffff
```

---

## 2. Diagram Arsitektur (High-Level)

```
┌─────────────────────────────────────────────────────────────┐
│                        TRAVELLER (PWA)                       │
│              Tailwind CSS · Laravel Blade / Livewire         │
└───────────────────────────┬─────────────────────────────────┘
                            │ HTTP / WebSocket
┌───────────────────────────▼─────────────────────────────────┐
│                    LARAVEL 13 (Backend)                      │
│   Routes · Controllers · Models · Queue · Jobs · Events      │
│                                                              │
│  ┌──────────────┐  ┌────────────────┐  ┌─────────────────┐  │
│  │  Auth Module │  │ Itinerary Mod. │  │ Booking Module  │  │
│  └──────────────┘  └───────┬────────┘  └────────┬────────┘  │
│                            │ Queue Job           │           │
│                    ┌───────▼────────┐   ┌────────▼────────┐ │
│                    │  AI Agent Job  │   │ Payment Gateway │ │
│                    └───────┬────────┘   └─────────────────┘ │
└───────────────────────────┬─────────────────────────────────┘
                            │ REST / JSON
        ┌───────────────────▼──────────────────┐
        │         OPENCODE SERVER (AI)          │
        │  Terima prompt → Proses → Return JSON │
        └──────────────────────────────────────┘

┌──────────────────────┐     ┌───────────────────────────────┐
│  MERCHANT DASHBOARD  │     │      ADMIN DASHBOARD          │
│  Laravel Filament V5 │     │      Laravel Filament V5      │
└──────────────────────┘     └───────────────────────────────┘

                    ┌──────────────┐
                    │  MySQL DB    │
                    └──────────────┘
```

---

## 3. Modul Backend (Laravel)

### 3.1 Auth Module
- Login / Register (email + password)
- Google OAuth via Laravel Socialite
- Role-based access: `traveller`, `merchant`, `officer`, `manager`, `superadmin`
- Middleware per role

### 3.2 Profiling Module
- Input & simpan data profiling traveller
- Validasi field mandatory (kota wisata, tgl lahir)

### 3.3 AI / Itinerary Module
- Terima input profiling → buat prompt terstruktur
- Dispatch ke Queue → Job memanggil OpenCode Server API
- Terima response JSON → parsing & simpan sebagai itinerary
- Itinerary bisa diedit traveller sebelum dikonfirmasi

### 3.4 Booking Module
- Integrasi dengan merchant (hotel, restoran, wisata, transport)
- Cek ketersediaan (availability check) real-time
- Konfirmasi booking → buat order → notifikasi merchant

### 3.5 Payment Module
- Integrasi Midtrans (MVP)
- Callback/webhook handler
- Update status transaksi & kirim voucher

### 3.6 Notification Module
- Email reminder (Laravel Mail + Queue)
- Web Push notification
- In-app notifikasi via dashboard

### 3.7 Wallet & Withdrawal Module (Merchant)
- Saldo masuk setelah traveller bayar
- Hold T+3 hari setelah layanan selesai
- Request withdrawal → proses transfer

### 3.8 SOS Module
- Traveller klik tombol SOS
- Kirim lokasi GPS + alert ke admin & kontak darurat

### 3.9 AI Training Data Module
- Terima upload bukti transaksi dari traveller
- Simpan sebagai dataset → feed ke proses fine-tuning / RAG AI

---

## 4. Laravel Filament V5 (Dashboard)

### Admin Dashboard
- Panel: `/admin`
- Resources: Traveller, Merchant, Officer, Manager, Itinerary, Transaksi
- Role gate: `officer`, `manager`, `superadmin`
- Konfigurasi app (superadmin only)

### Merchant Dashboard
- Panel: `/merchant`
- Resources: Booking, Reservasi, Inventori (kamar/kendaraan), Wallet, Laporan
- Role gate: `merchant`

---

## 5. OpenCode Server (AI Agent)

### Alur Komunikasi
```
1. Laravel Job menyiapkan payload JSON dari data profiling traveller
2. Job mengirim POST request ke OpenCode Server endpoint
3. OpenCode memproses prompt → memanggil model AI
4. Response dikembalikan dalam format JSON terstruktur
5. Laravel mem-parsing JSON → menyimpan ke tabel itineraries & itinerary_items
6. Frontend menampilkan itinerary yang sudah tervisualisasi
```

### Format Request (Contoh)
```json
{
  "destination": "Bali, Indonesia",
  "birth_date": "1998-05-12",
  "hobbies": ["photography", "culinary"],
  "budget": 5000000,
  "duration_days": 4,
  "interests": ["beach", "local culture"]
}
```

### Format Response yang Diharapkan
```json
{
  "itinerary": {
    "title": "4 Hari di Bali",
    "total_estimated_budget": 4800000,
    "days": [
      {
        "day": 1,
        "date": "2025-08-01",
        "schedule": [
          {
            "time": "14:00",
            "type": "hotel",
            "name": "Kuta Beach Inn",
            "description": "...",
            "estimated_cost": 450000,
            "bookable": true
          },
          {
            "time": "19:00",
            "type": "restaurant",
            "name": "Warung Made",
            "description": "...",
            "estimated_cost": 120000,
            "bookable": true
          }
        ]
      }
    ]
  }
}
```

---

## 6. Queue & Job Strategy

| Job | Trigger | Driver |
|-----|---------|--------|
| `GenerateItineraryJob` | Traveller klik "Proses" | database/Redis |
| `SendReminderJob` | Scheduler (H-3, H-1) | database/Redis |
| `ProcessWithdrawalJob` | Request withdrawal merchant | database/Redis |
| `TrainingDataUploadJob` | Upload bukti transaksi | database/Redis |

---

## 7. Keamanan

- HTTPS wajib di semua environment
- CSRF protection (Laravel default)
- Rate limiting pada endpoint AI & payment
- Validasi & sanitasi semua input
- File upload: validasi MIME type, simpan di private storage
- Google OAuth: validasi token via Socialite

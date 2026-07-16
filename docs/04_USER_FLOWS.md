# User Flows — TruSaba

---

## 1. Traveller Flow

### 1.1 Registrasi & Login
```
[Landing Page]
    │
    ├── "Daftar" ──→ Form (nama, email, password) ──→ Verifikasi Email ──→ Dashboard
    │
    └── "Masuk dengan Google" ──→ Google OAuth ──→ Dashboard
```

### 1.2 Pembuatan Itinerary (Core Flow)
```
[Dashboard Traveller]
    │
    └── "Buat Itinerary Baru"
          │
          ▼
    [Form Profiling]
    - Kota/negara tujuan wisata *
    - Tanggal keberangkatan & kepulangan *
    - Tanggal lahir *
    - Jumlah peserta
    - Budget (opsional)
    - Hobi & minat
    - Preferensi lainnya
          │
          ▼
    [Klik "Proses"]
          │
          ▼
    [Loading / Antrian AI]
    - Job masuk ke queue
    - OpenCode Server diproses
    - Polling status dari FE
          │
          ▼
    [Itinerary Ditampilkan]
    - Tampilan per hari
    - Setiap item: nama, waktu, estimasi biaya, tombol booking
    - Total estimasi budget
          │
    ┌─────┴─────┐
    │           │
"Edit"       "Konfirmasi & Simpan"
    │                │
    ▼                ▼
[Edit item]   [Itinerary tersimpan di Dashboard]
    │
    └── kembali ke tampilan itinerary
```

### 1.3 Booking dari Itinerary
```
[Item Itinerary — misal "Hotel Kuta Beach Inn"]
    │
    └── "Booking Sekarang"
          │
          ▼
    [Detail & Pilihan]
    - Pilih jenis kamar (jika hotel)
    - Cek ketersediaan tanggal
    - Ringkasan harga
          │
          ▼
    [Checkout & Payment]
    - Pilih metode pembayaran
    - Redirect ke Midtrans
          │
          ▼
    [Payment Callback]
    - Status: berhasil / gagal
          │
          ▼
    [Voucher & Konfirmasi]
    - Voucher tersimpan di Dashboard
    - Email konfirmasi terkirim
    - Merchant mendapat notifikasi
```

### 1.4 Selama Berwisata
```
[H-3 / H-1]
    └── Reminder email + notifikasi push terkirim ke traveller

[Hari H]
    └── Dashboard menampilkan jadwal hari ini
          │
          ├── Notifikasi per jadwal (checkin, makan siang, wisata, dll)
          ├── Akses voucher & bukti reservasi
          └── Upload bukti transaksi lapangan

[SOS]
    └── Klik tombol SOS → kirim lokasi + alert ke admin & kontak darurat
```

### 1.5 History
```
[Dashboard] → "Riwayat Perjalanan"
    ├── List itinerary (selesai/berjalan)
    ├── Detail itinerary per perjalanan
    └── History transaksi per perjalanan
```

---

## 2. Merchant Flow

### 2.1 Onboarding (via Admin)
```
Admin TruSaba membuat akun merchant
    │
    └── Merchant menerima email aktivasi
          │
          ▼
    Login → Setup profil merchant
          │
          ▼
    Input inventori:
    - Hotel: jenis kamar, jumlah, harga
    - Transport: jenis & jumlah kendaraan, harga
    - Restoran: kapasitas & slot
```

### 2.2 Menerima Booking
```
[Traveller melakukan booking]
    │
    ▼
[Merchant Dashboard]
    └── Notifikasi booking masuk
          │
          ▼
    Detail booking tampil:
    - Nama traveller
    - Tanggal check-in/reservasi
    - Jenis kamar/kendaraan/slot
    - Total pembayaran
          │
    ┌─────┴─────┐
    │           │
"Terima"    "Tolak" (dengan alasan)
    │
    ▼
Konfirmasi terkirim ke traveller
```

### 2.3 Verifikasi Checkin
```
[Hari H — traveller datang]
    │
    └── Traveller tunjukkan QR / kode voucher di aplikasi
          │
          ▼
    Merchant scan / input kode
          │
          ▼
    Status booking → "Checked In"
          │
          ▼
    Setelah selesai layanan:
    Update status → "Completed"
          │
          ▼
    Saldo masuk ke wallet (hold T+3)
```

### 2.4 Withdrawal
```
[Wallet Dashboard]
    └── Saldo tersedia (T+3 sudah lewat)
          │
          ▼
    "Request Withdrawal"
    - Input nominal
    - Pilih rekening bank
          │
          ▼
    Admin memproses → Transfer dilakukan
          │
          ▼
    Status withdrawal: Diproses / Selesai
```

---

## 3. Admin Flow

### 3.1 Officer Merchant
```
Login → Dashboard Officer
    ├── Lihat daftar merchant yang dibawahi
    ├── Monitor booking & transaksi per merchant
    └── Eskalasi isu ke Manager
```

### 3.2 Manager
```
Login → Dashboard Manager
    ├── Lihat semua Officer di bawahnya
    ├── Overview merchant & transaksi
    └── Laporan agregat
```

### 3.3 Superadmin
```
Login → Dashboard Superadmin
    ├── Overview seluruh platform
    │     ├── Total traveller aktif
    │     ├── Total merchant
    │     ├── Total transaksi & volume
    │     └── Itinerary generated
    │
    ├── Manajemen User (CRUD semua role)
    ├── Manajemen Merchant
    ├── Monitor SOS aktif
    ├── Konfigurasi aplikasi
    └── Konfigurasi AI agent
```

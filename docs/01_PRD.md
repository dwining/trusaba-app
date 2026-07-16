# Product Requirements Document — TruSaba

## 1. Visi Produk

TruSaba adalah aplikasi travel berbasis AI yang ditujukan untuk **traveller muda**. Tujuannya adalah memberikan kemudahan penuh dalam merencanakan perjalanan wisata — dari pembuatan itinerary otomatis hingga pendampingan real-time selama berwisata.

**Tagline:** *Yakin, Senang, Nyaman dalam Bertraveling.*

---

## 2. Aktor & Peran

### 2.1 Traveller
Pengguna utama yang merencanakan dan menjalani perjalanan.

**Kebutuhan utama:**
- Mendapatkan itinerary personal berbasis AI
- Booking hotel, tiket wisata, restoran, dan transportasi dari satu aplikasi
- Pendampingan real-time selama berwisata (notifikasi, jadwal, voucher)
- Menyimpan bukti transaksi dan history perjalanan
- Akses SOS dan AI Customer Service

### 2.2 Merchant
Penyedia layanan wisata: hotel, restoran, kafe, tempat wisata, rental kendaraan.

**Kebutuhan utama:**
- Menerima dan mengelola pesanan/reservasi dari traveller
- Verifikasi checkin/reservasi via aplikasi
- Manajemen ketersediaan (kamar, kendaraan, meja)
- Wallet saldo & withdrawal (T+3 hari setelah layanan selesai)
- Laporan transaksi di dashboard

### 2.3 Admin TruSaba
Operator platform dengan struktur role bertingkat.

**Role hierarchy:**
```
Superadmin
  └── Manager
        └── Officer Merchant
```

**Kebutuhan utama:**
- Monitoring semua aktivitas traveller & merchant
- Manajemen user (traveller & merchant)
- Konfigurasi aplikasi (superadmin)
- Laporan & audit transaksi

---

## 3. Fitur Utama per Aktor

### 3.1 Traveller

#### Onboarding & Profiling
- [ ] Registrasi (email/password + Google OAuth)
- [ ] Input data profiling:
  - Lokasi/kota/negara wisata *(mandatory)*
  - Tanggal lahir *(mandatory)*
  - Hobi & minat
  - Budget perjalanan
  - Lama traveling
  - Jumlah peserta

#### AI Itinerary Generator
- [ ] Generate itinerary otomatis via AI agent
- [ ] Rekomendasi: hotel, area wisata, restoran, oleh-oleh, transportasi
- [ ] Jadwal harian terstruktur
- [ ] Estimasi biaya per item & total budget
- [ ] Itinerary dapat diedit/disesuaikan traveller

#### Booking & Reservasi (dari itinerary)
- [ ] Booking hotel
- [ ] Beli tiket tempat wisata
- [ ] Reservasi restoran
- [ ] Booking rental kendaraan
- [ ] Payment gateway terintegrasi

#### Pendampingan Wisata (Saat H-Hari)
- [ ] Reminder email & notifikasi dashboard sebelum keberangkatan
- [ ] Notifikasi jadwal harian (checkin, wisata, makan, dll)
- [ ] Akses voucher hotel, tiket, bukti reservasi
- [ ] Upload bukti transaksi lapangan (dijadikan data training AI)

#### Fasilitas Tambahan
- [ ] Chat dengan AI Customer Service
- [ ] Tombol SOS (darurat / tersesat)
- [ ] Dashboard history perjalanan & transaksi

---

### 3.2 Merchant

#### Manajemen Pesanan
- [ ] Dashboard penerimaan booking/reservasi
- [ ] Verifikasi checkin via QR atau kode unik di aplikasi
- [ ] Update status layanan (selesai/pending/cancel)

#### Manajemen Inventori
- [ ] Hotel: manajemen jumlah & jenis kamar, ketersediaan per tanggal
- [ ] Rental: manajemen jumlah & jenis kendaraan, ketersediaan per tanggal
- [ ] Restoran: manajemen kapasitas & slot reservasi

#### Keuangan
- [ ] Wallet saldo merchant
- [ ] Withdrawal T+3 hari setelah layanan selesai
- [ ] Laporan transaksi lengkap

---

### 3.3 Admin TruSaba

#### Monitoring
- [ ] Overview semua aktivitas platform (traveller, merchant, transaksi)
- [ ] Detail itinerary per traveller

#### Manajemen User
- [ ] CRUD Traveller
- [ ] CRUD Merchant
- [ ] CRUD Admin (Officer, Manager, Superadmin)

#### Konfigurasi (Superadmin only)
- [ ] Konfigurasi parameter aplikasi
- [ ] Konfigurasi AI agent
- [ ] Konfigurasi payment gateway

---

## 4. Non-Functional Requirements

| Aspek | Ketentuan |
|-------|-----------|
| Platform | PWA (Progressive Web App) |
| Responsivitas | Mobile-first |
| Keamanan | Auth JWT / session Laravel, HTTPS |
| Performa | Queue untuk proses AI (tidak blocking UI) |
| Skalabilitas | Fase MVP → bisa dikembangkan ke native app |
| Bahasa UI | Indonesia (MVP) |

---

## 5. Batasan MVP

- Tidak ada native mobile app (hanya PWA)
- Payment gateway: 1 provider saja (misal Midtrans)
- AI hanya untuk pembuatan itinerary (bukan real-time dynamic update)
- SOS: tombol yang mengirim notifikasi + lokasi ke admin / kontak darurat
- Merchant onboarding dilakukan manual oleh Admin (tidak self-register)

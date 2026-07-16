# AI Agent Design — TruSaba

## 1. Peran AI dalam Aplikasi

AI adalah **otak utama** TruSaba. Fungsinya:
1. **Itinerary Generator** — membuat rencana perjalanan personal berdasarkan input traveller
2. **AI Customer Service** — menjawab pertanyaan traveller via chat
3. *(Future)* **Dynamic Itinerary Update** — memperbarui rekomendasi berdasarkan kondisi real-time

---

## 2. Arsitektur AI (MVP)

```
[Laravel Backend]
      │
      │  POST payload JSON
      ▼
[OpenCode Server]
      │  (Berperan sebagai AI agent orchestrator)
      │  → Meneruskan ke model AI (LLM)
      │
      ▼
[LLM Response]
      │
      │  Return JSON terstruktur
      ▼
[Laravel: Parsing & Simpan ke DB]
      │
      ▼
[Frontend: Visualisasi Itinerary]
```

---

## 3. GenerateItineraryJob

### File: `app/Jobs/GenerateItineraryJob.php`

**Alur kerja:**
```php
1. Terima $itinerary_id dari dispatcher
2. Ambil data itinerary + profil traveller dari DB
3. Bangun prompt terstruktur (lihat seksi 4)
4. Kirim POST ke OpenCode Server endpoint
5. Terima response JSON
6. Parsing JSON → simpan ke itinerary_items
7. Update status itinerary → 'draft' (siap ditampilkan)
8. Dispatch notifikasi ke traveller
```

**Retry & Error Handling:**
```php
public $tries = 3;
public $backoff = [30, 60, 120]; // detik

public function failed(Throwable $exception): void
{
    // Update status itinerary → 'failed'
    // Kirim notifikasi gagal ke traveller
}
```

---

## 4. Prompt Template

### System Prompt (dikirim ke AI)
```
Kamu adalah asisten perencana perjalanan wisata profesional untuk traveller Indonesia.
Tugasmu adalah membuat itinerary perjalanan yang personal, realistis, dan menarik.

Format respons HARUS berupa JSON valid sesuai schema yang diberikan.
Jangan tambahkan teks atau penjelasan di luar JSON.
Gunakan bahasa Indonesia untuk semua deskripsi.
```

### User Prompt (dibangun dari data input)
```
Buatkan itinerary perjalanan wisata dengan detail berikut:

TUJUAN: {destination}
TANGGAL: {start_date} sampai {end_date} ({duration_days} hari)
PESERTA: {participants} orang
BUDGET TOTAL: Rp {budget}
USIA TRAVELLER: {age} tahun
HOBI: {hobbies}
MINAT: {interests}

Sertakan rekomendasi untuk setiap hari:
- Hotel / penginapan (dengan estimasi harga per malam)
- Tempat wisata (dengan estimasi harga tiket dan waktu kunjungan)
- Restoran untuk sarapan, makan siang, dan makan malam
- Transportasi lokal
- Rekomendasi oleh-oleh (di hari terakhir)

Estimasikan biaya setiap item secara realistis sesuai budget.
Buat jadwal yang mempertimbangkan jarak antar lokasi dan waktu tempuh.

Kembalikan dalam format JSON berikut:
{json_schema}
```

---

## 5. JSON Schema Response AI

```json
{
  "title": "string — judul itinerary",
  "destination": "string",
  "total_estimated_budget": "integer — total dalam Rupiah",
  "currency": "IDR",
  "summary": "string — ringkasan singkat perjalanan",
  "days": [
    {
      "day": "integer — nomor hari (1, 2, dst)",
      "date": "string — YYYY-MM-DD",
      "theme": "string — tema hari ini, misal 'Hari Pantai'",
      "schedule": [
        {
          "time": "string — HH:MM",
          "type": "enum: hotel|restaurant|attraction|transport|shopping|other",
          "name": "string — nama tempat/layanan",
          "description": "string — deskripsi singkat",
          "location": "string — alamat atau area",
          "estimated_cost": "integer — estimasi biaya dalam Rupiah",
          "duration_minutes": "integer — estimasi durasi kunjungan",
          "is_bookable": "boolean — bisa dibooking via TruSaba",
          "tips": "string|null — tips atau catatan khusus"
        }
      ],
      "daily_estimated_cost": "integer — total estimasi biaya hari ini"
    }
  ],
  "packing_tips": ["string — tips bawaan barang"],
  "general_tips": ["string — tips umum untuk tujuan wisata ini"]
}
```

---

## 6. Matching AI Response ke Merchant

Setelah itinerary berhasil di-generate dan disimpan, sistem menjalankan proses **merchant matching**:

```
Untuk setiap itinerary_item dengan is_bookable = true:
    1. Cari merchant di DB berdasarkan:
       - type cocok (hotel/restaurant/attraction/transport)
       - kota/area cocok
       - ketersediaan tanggal
    2. Jika ada merchant yang cocok:
       - Update itinerary_item.merchant_id
    3. Jika tidak ada:
       - Biarkan kosong (traveller bisa arrange sendiri)
```

---

## 7. AI Customer Service (Chat)

### Endpoint
`POST /chat`

### Alur
```
[Traveller kirim pesan]
      │
      ▼
[Backend ambil context: profil + itinerary aktif]
      │
      ▼
[Kirim ke OpenCode dengan system prompt CS + conversation history]
      │
      ▼
[Stream response ke traveller]
```

### System Prompt CS
```
Kamu adalah customer service TruSaba yang ramah dan helpful.
Kamu membantu traveller dalam hal:
- Pertanyaan tentang itinerary mereka
- Informasi tentang destinasi wisata
- Pertanyaan seputar booking dan pembayaran
- Tips dan saran traveling

Konteks traveller saat ini:
{user_context}

Itinerary aktif:
{active_itinerary_summary}

Jawab dalam bahasa Indonesia dengan ramah dan singkat.
```

---

## 8. AI Training Data (Upload Bukti Transaksi)

Upload bukti transaksi dari traveller disimpan sebagai dataset untuk:
1. **Validasi estimasi biaya** — apakah estimasi AI akurat vs harga nyata di lapangan
2. **Fine-tuning / RAG** — data harga real-time dari traveller dijadikan knowledge base
3. **Improvement loop** — semakin banyak data, semakin akurat estimasi AI

### Pipeline (Future — post-MVP)
```
[Upload file] → [OCR ekstrak amount] → [Map ke itinerary_item] 
→ [Simpan ke training_dataset] → [Periodic fine-tuning / RAG update]
```

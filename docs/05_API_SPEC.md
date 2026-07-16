# API Specification — TruSaba (Laravel 13)

> Base URL: `https://api.trusaba.id/api/v1`  
> Auth: Bearer Token (Laravel Sanctum)  
> Format: JSON

---

## 1. Auth

### `POST /auth/register`
Registrasi traveller baru.

**Request:**
```json
{
  "name": "Budi Santoso",
  "email": "budi@email.com",
  "password": "rahasia123",
  "password_confirmation": "rahasia123"
}
```

**Response 201:**
```json
{
  "message": "Registrasi berhasil. Silakan verifikasi email Anda.",
  "user": { "id": 1, "name": "Budi Santoso", "email": "budi@email.com" }
}
```

---

### `POST /auth/login`
Login dengan email & password.

**Request:**
```json
{ "email": "budi@email.com", "password": "rahasia123" }
```

**Response 200:**
```json
{
  "token": "1|xxxxxxxxxxx",
  "user": { "id": 1, "name": "Budi Santoso", "role": "traveller" }
}
```

---

### `GET /auth/google/redirect`
Redirect ke Google OAuth.

### `GET /auth/google/callback`
Callback Google OAuth → return token.

---

### `POST /auth/logout`
🔒 Requires auth.

---

## 2. Traveller Profile

### `GET /profile`
🔒 Ambil profil traveller yang login.

### `PUT /profile`
🔒 Update profil traveller.

**Request:**
```json
{
  "birth_date": "1998-05-12",
  "phone": "081234567890",
  "hobbies": ["photography", "culinary"],
  "interests": ["beach", "local culture"],
  "default_budget": 5000000
}
```

---

## 3. Itinerary

### `POST /itineraries/generate`
🔒 Buat itinerary baru via AI.

**Request:**
```json
{
  "destination": "Bali, Indonesia",
  "start_date": "2025-08-01",
  "end_date": "2025-08-04",
  "participants": 2,
  "budget": 10000000,
  "hobbies": ["photography"],
  "interests": ["beach", "culture"]
}
```

**Response 202:** *(Accepted — diproses via Queue)*
```json
{
  "message": "Itinerary sedang diproses.",
  "itinerary_id": 42,
  "status": "processing"
}
```

---

### `GET /itineraries/{id}/status`
🔒 Cek status pemrosesan itinerary.

**Response:**
```json
{
  "itinerary_id": 42,
  "status": "completed",   // "processing" | "completed" | "failed"
  "itinerary": { ... }     // ada jika status = completed
}
```

---

### `GET /itineraries`
🔒 List semua itinerary milik traveller yang login.

### `GET /itineraries/{id}`
🔒 Detail itinerary beserta semua item per hari.

### `PUT /itineraries/{id}`
🔒 Update itinerary (edit manual oleh traveller).

### `DELETE /itineraries/{id}`
🔒 Hapus itinerary (jika belum ada booking aktif).

---

## 4. Booking

### `POST /bookings`
🔒 Buat booking dari item itinerary.

**Request:**
```json
{
  "itinerary_item_id": 15,
  "merchant_id": 3,
  "booking_type": "hotel",
  "check_in_date": "2025-08-01",
  "check_out_date": "2025-08-04",
  "quantity": 1,
  "resource_detail": { "room_type": "Deluxe" }
}
```

**Response 201:**
```json
{
  "booking_id": 101,
  "status": "pending",
  "amount": 1350000,
  "payment_url": "https://payment.midtrans.com/xxx"
}
```

---

### `GET /bookings`
🔒 List semua booking milik traveller.

### `GET /bookings/{id}`
🔒 Detail booking + voucher (jika sudah paid).

---

## 5. Merchant Availability

### `GET /merchants/{id}/availability`
Cek ketersediaan merchant pada tanggal tertentu.

**Query:** `?date=2025-08-01&type=room&resource_id=5`

**Response:**
```json
{
  "available": true,
  "available_qty": 3
}
```

---

## 6. Transactions

### `POST /transactions/callback`
Webhook dari Midtrans (tidak perlu auth token).

### `GET /transactions`
🔒 List transaksi milik traveller yang login.

---

## 7. Expense Uploads

### `POST /expense-uploads`
🔒 Upload bukti transaksi lapangan.

**Form-data:**
```
file: [binary]
itinerary_id: 42
booking_id: 101 (optional)
amount: 150000
description: "Makan siang di Warung Made"
```

---

## 8. Notifications

### `GET /notifications`
🔒 List notifikasi milik user yang login.

### `PUT /notifications/{id}/read`
🔒 Tandai notifikasi sebagai dibaca.

### `PUT /notifications/read-all`
🔒 Tandai semua notifikasi sebagai dibaca.

---

## 9. SOS

### `POST /sos`
🔒 Kirim alert SOS darurat.

**Request:**
```json
{
  "latitude": -8.670458,
  "longitude": 115.212629,
  "message": "Saya tersesat di area Ubud"
}
```

---

## 10. Merchant API (Prefix: `/merchant`)

> Semua endpoint merchant menggunakan middleware role `merchant`.

### `GET /merchant/bookings`
List booking masuk ke merchant yang login.

### `PUT /merchant/bookings/{id}/confirm`
Konfirmasi booking.

### `PUT /merchant/bookings/{id}/checkin`
Proses checkin traveller (verifikasi voucher).

### `PUT /merchant/bookings/{id}/complete`
Tandai layanan selesai.

### `GET /merchant/wallet`
Info saldo wallet merchant.

### `POST /merchant/withdrawals`
Request withdrawal.

**Request:**
```json
{
  "amount": 2000000,
  "bank_name": "BCA",
  "account_number": "1234567890",
  "account_name": "PT Merchant Jaya"
}
```

### `GET /merchant/rooms`
List kamar milik merchant.

### `POST /merchant/rooms`
Tambah tipe kamar baru.

### `PUT /merchant/rooms/{id}`
Update tipe kamar.

### `GET /merchant/vehicles`
List kendaraan rental.

### `POST /merchant/vehicles`
Tambah kendaraan.

---

## 11. Error Response Standard

```json
{
  "message": "Deskripsi error",
  "errors": {
    "field_name": ["Pesan validasi"]
  }
}
```

| HTTP Code | Arti |
|-----------|------|
| 200 | OK |
| 201 | Created |
| 202 | Accepted (async) |
| 400 | Bad Request |
| 401 | Unauthenticated |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

# Database Schema — TruSaba (MySQL)

> Semua tabel menggunakan `id` BIGINT UNSIGNED AUTO_INCREMENT sebagai primary key.  
> Semua tabel memiliki kolom `created_at` dan `updated_at` (Laravel timestamps).

---

## 1. Users & Auth

### `users`
```sql
id               BIGINT PK
name             VARCHAR(100)
email            VARCHAR(100) UNIQUE
password         VARCHAR(255) NULLABLE  -- null jika login via Google
google_id        VARCHAR(100) NULLABLE
avatar           VARCHAR(255) NULLABLE
role             ENUM('traveller','merchant','officer','manager','superadmin')
is_active        BOOLEAN DEFAULT true
email_verified_at TIMESTAMP NULLABLE
remember_token   VARCHAR(100)
created_at / updated_at
```

### `traveller_profiles`
```sql
id               BIGINT PK
user_id          BIGINT FK → users.id
birth_date       DATE
phone            VARCHAR(20) NULLABLE
hobbies          JSON NULLABLE          -- ["photography","culinary"]
interests        JSON NULLABLE          -- ["beach","culture"]
default_budget   BIGINT NULLABLE
created_at / updated_at
```

---

## 2. Merchant

### `merchants`
```sql
id               BIGINT PK
user_id          BIGINT FK → users.id
name             VARCHAR(150)
type             ENUM('hotel','restaurant','cafe','attraction','transport','other')
address          TEXT
city             VARCHAR(100)
province         VARCHAR(100)
country          VARCHAR(100)
phone            VARCHAR(20)
description      TEXT NULLABLE
logo             VARCHAR(255) NULLABLE
is_active        BOOLEAN DEFAULT true
wallet_balance   BIGINT DEFAULT 0       -- dalam Rupiah, disimpan sebagai integer
created_at / updated_at
```

### `merchant_rooms`  *(untuk hotel)*
```sql
id               BIGINT PK
merchant_id      BIGINT FK → merchants.id
room_type        VARCHAR(100)           -- "Deluxe", "Suite", dll
total_rooms      INT
price_per_night  BIGINT
description      TEXT NULLABLE
created_at / updated_at
```

### `merchant_vehicles`  *(untuk rental)*
```sql
id               BIGINT PK
merchant_id      BIGINT FK → merchants.id
vehicle_type     VARCHAR(100)           -- "Motor", "Mobil", "Van"
vehicle_name     VARCHAR(100)
total_units      INT
price_per_day    BIGINT
created_at / updated_at
```

### `merchant_availability`
```sql
id               BIGINT PK
merchant_id      BIGINT FK → merchants.id
resource_type    ENUM('room','vehicle','slot')
resource_id      BIGINT                 -- FK ke room / vehicle / slot
date             DATE
available_qty    INT
created_at / updated_at
```

---

## 3. Itinerary

### `itineraries`
```sql
id               BIGINT PK
user_id          BIGINT FK → users.id
title            VARCHAR(200)
destination      VARCHAR(200)
start_date       DATE
end_date         DATE
duration_days    INT
total_participants INT DEFAULT 1
budget_input     BIGINT NULLABLE        -- budget yang diinput user
estimated_budget BIGINT NULLABLE        -- estimasi dari AI
status           ENUM('draft','confirmed','ongoing','completed','cancelled') DEFAULT 'draft'
ai_raw_response  LONGTEXT NULLABLE      -- simpan raw JSON dari AI
created_at / updated_at
```

### `itinerary_items`
```sql
id               BIGINT PK
itinerary_id     BIGINT FK → itineraries.id
day_number       INT
schedule_time    TIME
type             ENUM('hotel','restaurant','attraction','transport','shopping','other')
name             VARCHAR(200)
description      TEXT NULLABLE
location         VARCHAR(255) NULLABLE
estimated_cost   BIGINT DEFAULT 0
is_bookable      BOOLEAN DEFAULT false
merchant_id      BIGINT FK → merchants.id NULLABLE
booking_id       BIGINT NULLABLE        -- diisi setelah booking
sort_order       INT DEFAULT 0
created_at / updated_at
```

---

## 4. Booking & Transaksi

### `bookings`
```sql
id               BIGINT PK
user_id          BIGINT FK → users.id
itinerary_id     BIGINT FK → itineraries.id NULLABLE
merchant_id      BIGINT FK → merchants.id
itinerary_item_id BIGINT FK → itinerary_items.id NULLABLE
booking_type     ENUM('hotel','restaurant','attraction','transport','other')
check_in_date    DATE NULLABLE
check_out_date   DATE NULLABLE
booking_date     DATE NULLABLE          -- untuk restoran/wisata
quantity         INT DEFAULT 1
resource_detail  JSON NULLABLE          -- {"room_type":"Deluxe","vehicle":"Mobil"}
amount           BIGINT
status           ENUM('pending','confirmed','checked_in','completed','cancelled') DEFAULT 'pending'
voucher_code     VARCHAR(50) NULLABLE
notes            TEXT NULLABLE
created_at / updated_at
```

### `transactions`
```sql
id               BIGINT PK
booking_id       BIGINT FK → bookings.id
user_id          BIGINT FK → users.id
amount           BIGINT
payment_method   VARCHAR(50)
payment_gateway  VARCHAR(50) DEFAULT 'midtrans'
gateway_trx_id   VARCHAR(100) NULLABLE  -- ID dari Midtrans
status           ENUM('pending','paid','failed','refunded') DEFAULT 'pending'
paid_at          TIMESTAMP NULLABLE
created_at / updated_at
```

---

## 5. Wallet & Withdrawal

### `wallet_transactions`
```sql
id               BIGINT PK
merchant_id      BIGINT FK → merchants.id
transaction_id   BIGINT FK → transactions.id NULLABLE
type             ENUM('credit','debit')
amount           BIGINT
balance_after    BIGINT
description      VARCHAR(255)
available_at     TIMESTAMP NULLABLE     -- T+3 hari
created_at / updated_at
```

### `withdrawal_requests`
```sql
id               BIGINT PK
merchant_id      BIGINT FK → merchants.id
amount           BIGINT
bank_name        VARCHAR(100)
account_number   VARCHAR(50)
account_name     VARCHAR(100)
status           ENUM('pending','processed','rejected') DEFAULT 'pending'
processed_at     TIMESTAMP NULLABLE
notes            TEXT NULLABLE
created_at / updated_at
```

---

## 6. Notifikasi & Reminder

### `notifications` *(Laravel default)*
```sql
id               UUID PK
type             VARCHAR(255)
notifiable_type  VARCHAR(255)
notifiable_id    BIGINT
data             TEXT
read_at          TIMESTAMP NULLABLE
created_at / updated_at
```

### `reminders`
```sql
id               BIGINT PK
user_id          BIGINT FK → users.id
itinerary_id     BIGINT FK → itineraries.id
remind_at        TIMESTAMP
type             ENUM('email','push','both')
message          TEXT
is_sent          BOOLEAN DEFAULT false
sent_at          TIMESTAMP NULLABLE
created_at / updated_at
```

---

## 7. Upload Bukti Transaksi (Training Data)

### `expense_uploads`
```sql
id               BIGINT PK
user_id          BIGINT FK → users.id
itinerary_id     BIGINT FK → itineraries.id NULLABLE
booking_id       BIGINT FK → bookings.id NULLABLE
file_path        VARCHAR(255)
amount           BIGINT NULLABLE
description      VARCHAR(255) NULLABLE
is_processed     BOOLEAN DEFAULT false  -- sudah diproses untuk AI training?
created_at / updated_at
```

---

## 8. Admin Hierarchy

### `admin_assignments`
```sql
id               BIGINT PK
manager_id       BIGINT FK → users.id   -- role: manager
officer_id       BIGINT FK → users.id   -- role: officer
created_at / updated_at
```

### `officer_merchants`
```sql
id               BIGINT PK
officer_id       BIGINT FK → users.id
merchant_id      BIGINT FK → merchants.id
created_at / updated_at
```

---

## 9. SOS Logs

### `sos_logs`
```sql
id               BIGINT PK
user_id          BIGINT FK → users.id
latitude         DECIMAL(10,8) NULLABLE
longitude        DECIMAL(11,8) NULLABLE
message          TEXT NULLABLE
status           ENUM('open','handled','closed') DEFAULT 'open'
handled_by       BIGINT FK → users.id NULLABLE
handled_at       TIMESTAMP NULLABLE
created_at / updated_at
```

---

## Index yang Direkomendasikan

```sql
-- Sering diquery bersamaan
INDEX idx_itinerary_user (user_id) ON itineraries
INDEX idx_itinerary_items_itinerary (itinerary_id) ON itinerary_items
INDEX idx_bookings_user (user_id) ON bookings
INDEX idx_bookings_merchant (merchant_id) ON bookings
INDEX idx_transactions_booking (booking_id) ON transactions
INDEX idx_availability_date (merchant_id, date) ON merchant_availability
INDEX idx_reminders_remind_at (remind_at, is_sent) ON reminders
```

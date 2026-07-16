# TruSaba — Master Documentation

> Dokumentasi ini dibuat untuk mendukung proses **vibecoding** aplikasi TruSaba secara bertahap (MVP).

---

## Daftar Dokumen

| File | Deskripsi |
|------|-----------|
| `01_PRD.md` | Product Requirements Document |
| `02_ARSITEKTUR.md` | Arsitektur teknis & stack |
| `03_DATABASE_SCHEMA.md` | Skema database MySQL |
| `04_USER_FLOWS.md` | User flow tiap aktor |
| `05_API_SPEC.md` | Spesifikasi endpoint API |
| `06_AI_AGENT.md` | Desain AI agent (OpenCode/itinerary) |
| `07_FASE_MVP.md` | Rencana pembangunan per fase |
| `08_PROMPT_TEMPLATES.md` | Template prompt untuk vibecoding |

---

## Ringkasan Aplikasi

**TruSaba** adalah travel companion app berbasis AI yang membantu traveller muda merencanakan dan menjalani perjalanan wisata — mulai dari pembuatan itinerary otomatis, booking layanan, hingga pendampingan real-time saat berwisata.

### Aktor Utama
- **Traveller** — pengguna akhir yang merencanakan dan menjalani wisata
- **Merchant** — penyedia layanan (hotel, restoran, transportasi, wisata)
- **Admin TruSaba** — operator platform dengan struktur role bertingkat

### Tech Stack MVP
- **Frontend:** PWA + Tailwind CSS
- **Backend:** PHP + Laravel 13
- **Dashboard:** Laravel Filament V5 (Admin & Merchant)
- **Database:** MySQL
- **AI Agent:** OpenCode Server
- **Auth:** Native + Google OAuth
- **Warna:** Primary `#066fda` · Secondary `#fcc415` · Background `#ffffff`

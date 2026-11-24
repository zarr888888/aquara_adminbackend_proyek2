# ARCHITECTURE.md - SAMUDERA Technical Architecture

## 1. Tech Stack Overview
- **Backend & Web Frontend:** Laravel 12 (PHP 8.3.24+), Filamentphp v3 (Admin Panel).
- **Database:** PostgreSQL (Heroku Postgres) - *Local dev menggunakan MySQL, pastikan kode ORM agnostik.*
- **Mobile Frontend:** Flutter (Dart).
- **Architecture Pattern:** MVC (Model-View-Controller).
- **Cloud Storage:** Cloudinary (Manajemen Aset Gambar Media).
- **Hosting/PaaS:** Heroku.

## 2. Core Structure (Laravel)
- `app/Http/Controllers/Api/` -> Tempat logika bisnis REST API untuk Flutter (misal: ForumController, AiConsultationController).
- `app/Filament/Resources/` -> Tempat logika antarmuka Dashboard Web Admin (Filament v3).
- `routes/web.php` & `routes/api.php` -> Manajemen rute aplikasi web dan API mobile.
- `database/migrations/` -> Skema tabel SAMUDERA (harus kompatibel PostgreSQL).
- `config/filesystems.php` -> Konfigurasi disk Cloudinary.
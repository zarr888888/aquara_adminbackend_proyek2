# AGENTS.md - Rules for AI Coding Agents (Cline / Claude Sonnet 5)

## 1. Project Identity & Architecture
- **App Name:** SAMUDERA (Laravel 12 Backend/Web + Flutter Mobile)
- **Primary Color:** Biru Laut, Putih, dan warna maritim.
- **Deployment Target:** Heroku (Ephemeral Filesystem) + Heroku Postgres.

## 2. Core Rules (NEVER DO THIS)
- ❌ **JANGAN PERNAH** memodifikasi folder `vendor/` atau `node_modules/`.
- ❌ **JANGAN PERNAH** menjalankan perintah terminal yang memakan banyak waktu tanpa izin (misal: `npm install` besar-besaran atau `composer update` total).
- ❌ **JANGAN PERNAH** menghapus fitur lama Proyek 2, kita hanya MENAMBAH dan MENGUPDATE.
- ❌ **JANGAN PERNAH** membuat file `Migration` baru jika kita bisa menggunakan `php artisan make:migration`.
- ❌ **JANGAN PERNAH** menyimpan file upload pengguna ke `storage/app/public`. Seluruh file gambar harus di-upload dan diakses melalui Cloudinary.
- ❌ **JANGAN PERNAH** menulis Raw Query MySQL (seperti `DB::raw()`) yang spesifik untuk MySQL. Gunakan Eloquent ORM atau Query Builder standar agar kompatibel dengan PostgreSQL.
- ❌ **JANGAN PERNAH** membuat view antarmuka admin secara manual jika bisa diselesaikan menggunakan Filament v3 Resources.

## 3. Database Rules & Storage
- Selalu patuhi struktur pada `docs/database_schema.md`.
- Jika disuruh menambah kolom, selalu buat file migration baru (jangan edit migration lama yang sudah di-migrate).
- Gunakan facade `Storage::disk('cloudinary')` untuk semua file handling.
- Semua migration file harus mendefinisikan tipe kolom yang aman untuk Postgres (contoh: gunakan `$table->text()` daripada tipe data spesifik yang hanya ada di MySQL).
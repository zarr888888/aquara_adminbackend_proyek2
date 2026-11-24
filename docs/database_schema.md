# Skema Database SAMUDERA (Fase 1 Update)

Dokumen ini adalah referensi struktur tabel utama untuk proyek SAMUDERA. Gunakan Eloquent standar yang kompatibel dengan PostgreSQL.

## 1. Tabel Eksisting: `katalog_penambak` (Perlu Diupdate)
Tabel ini sudah ada. Anda HANYA perlu membuat file migration baru untuk menambahkan 3 kolom berikut:
- `stok` (integer, default 0)
- `kategori` (enum/string: 'tawar', 'laut', 'olahan')
- `tipe_penjualan` (enum/string: 'eceran', 'grosir')

## 2. Tabel Baru: `lapor_darurat` (SOS Bahari)
Tabel ini baru dibuat untuk menampung pelaporan dari User ke Admin Dinas.
- `id` (Primary Key)
- `user_id` (Foreign Key ke tabel users)
- `jenis_sos` (string: contoh 'Wabah Penyakit', 'Pencemaran Limbah', 'Cuaca Ekstrem')
- `deskripsi` (text)
- `foto` (string, menampung URL dari Cloudinary)
- `status_laporan` (string/enum: 'Pending', 'Diproses', 'Selesai', default 'Pending')
- `admin_id` (Foreign Key ke tabel users/admin, nullable, untuk melacak admin yang memproses)
- `timestamps`

## 3. Tabel Baru: `pre_order` (Bursa Grosir)
Tabel ini baru dibuat untuk mencatat transaksi B2B skala besar yang berelasi dengan katalog.
- `id` (Primary Key)
- `user_id` (Foreign Key ke tabel users - pembeli)
- `katalog_id` (Foreign Key ke tabel katalog_penambak)
- `total_tonase` (integer/decimal, jumlah pesanan grosir)
- `tgl_pengambilan` (date)
- `status_bayar` (string/enum: 'Belum Bayar', 'DP', 'Lunas', default 'Belum Bayar')
- `timestamps`

## 4. Tabel Eksisting: `forum` (Informasi Relasi)
Tabel ini sudah ada. Pastikan endpoint API atau query di Controller menggunakan `user_id` untuk memfilter fitur "Forum Saya" (forum yang dibuat oleh user yang sedang login).

### Implementasi aktual komentar forum — 24 Oktober 2026

- Posting: `community_posts`; komentar: `community_comments`; like posting: `post_likes` (tidak diubah).
- Migration `2026_10_24_003711_add_threads_and_likes_to_community_comments.php`, dibuat melalui Artisan pada bootstrap testing terisolasi. **Sudah diterapkan pada MySQL lokal 127.0.0.1:3306/aquara_proyek2**, 24 Oktober 2026, setelah test disposable dan backup; status Ran/schema/count/fixture diverifikasi. Detail receipt dan backup ada pada TODO.
- `community_comments.parent_id`: nullable foreign ID ke `community_comments.id`, cascade on delete. Existing rows tetap null, tidak ada backfill dari @mention.
- Indeks `(community_post_id,parent_id,id)` untuk daftar komentar utama/urutan; indeks `(parent_id,id)` untuk pagination replies dan pemeriksaan cascade, termasuk PostgreSQL yang tidak otomatis mengindeks sisi referencing foreign key.
- API hanya menerima parent komentar utama pada posting sama saat create. Parent immutable; nesting lebih dari satu tingkat, self-parent, dan siklus via update ditolak. Aturan same-post/depth ditegakkan API, bukan constraint lintas-row database; penulis DB/admin baru wajib mengikuti invariannya.
- `comment_likes`: id, community_comment_id FK cascade, user_id FK users cascade, timestamps. UNIQUE `(community_comment_id,user_id)`; indeks FK dibuat oleh schema builder/database sesuai driver. Count dihitung dari relasi, tidak disimpan pada kolom counter.
- Hapus induk menghapus balasan dan semua like terkait; hapus balasan tidak menghapus induk. Hapus posting tetap cascade ke komentar.
- Kolom legacy `community_comments.user_id` tetap string; tidak diubah menjadi foreign ID dalam fitur ini.
- Migration menggunakan Schema Blueprint portable MySQL/PostgreSQL. Up/down diuji SQLite dan MySQL 8.0.30 disposable; concurrency like+like/unlike+unlike diuji dengan worker independen MySQL. PostgreSQL dan concurrent delete/reply masih tertunda. Down menyediakan ulang indeks standalone post bila InnoDB telah mengganti indeks implisit FK dengan indeks komposit. Rollback membuang relasi parent dan seluruh like komentar, tetapi mempertahankan isi komentar yang belum dihapus. Rollback pengembangan tidak dijalankan.
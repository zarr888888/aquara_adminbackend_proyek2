# SAMUDERA — lembar operasi RC 24 November 2026 (belum deploy)

Target baru: Apps & Containers, bukan VPS atau Heroku. Baseline backend 76a00cb, mobile 91ad816, keduanya dengan perubahan lokal. Jangan push worktree secara massal. Jangan impor data demo atau seed akun default.

## Setelah gate fungsional dan persetujuan akhir

1. Pilih commit yang sudah direview pada branch RC; izinkan Helipod GitHub App hanya membaca repo backend. Add Service → Git → GitHub → branch RC → root repo. Matikan deploy otomatis bila tersedia. Dockerfile root diprioritaskan Helipod; kandidat memakai Apache serta satu worker dan scheduler melalui supervisor (lihat RC-DELTA.md). Validasi image produksi dan health check sebelum deploy. Endpoint pemeriksaan tidak boleh mengungkap konfigurasi.
2. Usulan pembagian (bukan hasil benchmark): satu pod web API + Filament 1 vCPU / 1024 MB; satu pod MariaDB 11.4 1 vCPU / 1024 MB, volume persisten `/var/lib/mysql`, awal 16 GB. Sisakan kapasitas untuk image dan operasi. DB tanpa domain dan tanpa port publik; aplikasi memakai hostname internal yang diberikan dashboard. Uji data tetap ada sesudah restart dan redeploy. Jika tidak muat, jangan naikkan paket otomatis.
3. Variables privat: APP_ENV=production, APP_DEBUG=false, APP_URL=origin HTTPS aktual, DB_CONNECTION=mysql, DB_HOST=hostname internal, DB_PORT=3306, DB_DATABASE=nama DB rilis, SESSION_SECURE_COOKIE=true. APP_KEY stabil, password DB root dan aplikasi berbeda, Cloudinary, OTP dan provider aktif disuntikkan melalui secrets. Batasi user DB ke satu schema. Jangan mengirim nilai ke log, image, Git, atau APK. Tetapkan proxy tepercaya sesuai ingress aktual; uji cookie dan redirect HTTPS Filament.
4. Ambil backup sebelum perubahan schema. Jalankan `php artisan migrate --force` sekali dari release task terhadap DB rilis setelah target diverifikasi, bukan pada startup tiap replika. Jangan gunakan fresh/refresh atau seed demo. Provision Admin Dinas dan super_admin melalui saluran privat. Jalankan queue database dengan satu worker terpantau jika dipakai; jalankan scheduler satu kali per menit di satu instance, tanpa scheduler duplikat. Pantau RSS, restart, jobs gagal, volume dan latency.
5. `backup:run` sudah dijadwalkan harian tetapi tujuan lokal saja tidak cukup. Sebelum go-live sediakan dump client kompatibel dan target backup terpisah dari pod/volume, enkripsi dan simpan kunci pemulihan terpisah. Jadwalkan backup DB saja; jangan arsipkan .env atau salinan data pengguna lain. Periksa checksum, usia dan retensi. Latihan restore: buat volume dan DB kosong privat, verifikasi checksum, dekripsi secara privat, impor dump; cocokkan jumlah/baris sampel dan akses aplikasi tanpa pengiriman OTP/SOS. Ukur waktu pemulihan. Jangan menimpa volume asal. Jika gagal, batalkan cutover.
6. Verifikasi TLS, `/up`, penolakan endpoint privat tanpa sesi, login Filament dan URL API dengan HP di jaringan berbeda. Setelah URL sebenarnya terverifikasi, gunakan entry point `lib/main.dart` dan `flutter build apk --release --dart-define=SAMUDERA_API_URL=<origin-https-terverifikasi>/api`, tanpa split ABI. Verifikasi sertifikat dengan apksigner dan checksum SHA-256, sajikan APK dari URL HTTPS stabil; buat QR dari URL unduh, bukan URL backend. Scan, unduh, pasang dan login pada perangkat fisik. Jangan mengganti APK 4002.

Acuan: https://docs.helipod.io/docs/guides/deploy-from-github dan https://helipod.io/pricing . Estimasi bundle Micro satu bulan Rp125.250, cek checkout sebelum otorisasi. Backup luar pod, OTP, Cloudinary, domain dan pemakaian AI dihitung terpisah. Tidak ada otorisasi belanja melalui dokumen ini.

## Hasil verifikasi eksternal 24 November 2026

Dijalankan melalui `tests/Support/rc-external-verify.php` dengan file env kandidat privat (di luar repo, ACL terbatas; tidak ada nilai kredensial di log/git).

| Pemeriksaan | Hasil |
| --- | --- |
| Cloudinary (jalur trait produksi, folder `samudera/rc-verification`) | `CLOUDINARY_UPLOAD=OK host=res.cloudinary.com`, `CLOUDINARY_FETCH=200 type=image/png`, `CLOUDINARY_DELETE=OK` — upload, ambil URL aman, unduh, lalu hapus aset uji. |
| BMKG `XJ022` Karangsong | `status=available items=41 valid=2026-11-24T00:00:00+00:00..2026-11-27T00:00:00+00:00`. |
| BMKG `XJ021` Eretan | `status=available items=41 valid=2026-11-24T00:00:00+00:00..2026-11-27T00:00:00+00:00`. |
| Groq Asisten (`GOMBYANG_PROVIDER=groq`) | `GROQ=OK len=4` — satu request nyata, jawaban diterima. |
| SMTP | `MAIL_MAILER=smtp host=set` — pengirim nyata terkonfigurasi. |
| Harness OTP kandidat (`tests/Support/rc-external-verify.php`) | DIPERBAIKI 24 November 2026 — akar `OTP_EMAIL=FAIL`: drill hanya menjalankan dua migrasi, sehingga tabel `users` tidak punya `phone`, `otp`, `otp_expired_at` dan `OtpService::issue` gagal `Illuminate\Database\QueryException` (`table users has no column named phone`) sebelum ada percobaan kirim. Kini keenam nama migrasi produksi yang benar dijalankan berurutan dan skema diverifikasi per kolom sebelum kirim; kegagalan/skip juga menghasilkan exit code non-zero. |
| OTP email ke penguji | TERTUNDA: `RC_OTP_TEST_EMAIL` belum diisi di file kandidat. Satu-satunya gerbang verifikasi tersisa sebelum approval. |

### Kontrak exit code harness eksternal

`tests/Support/rc-external-verify.php`: **0** = setiap bagian yang diminta mencapai keluaran sukses yang diharapkan; **1** = ada bagian yang diminta gagal atau ter-skip sehingga gate belum terpenuhi; **2** = prasyarat gagal (file env kandidat hilang). Script menulis `RC_EXTERNAL_VERIFY=OK` atau `RC_EXTERNAL_VERIFY=FAIL <kode>` (ke stderr saat gagal) agar operator/CI tidak bisa membaca FAIL sebagai lulus.

### Validasi lokal 24 November 2026 (tanpa kredensial nyata, tanpa layanan eksternal)

SMTP stub loopback `127.0.0.1` + database kandidat disposable; tidak ada trafik ke internet.

- Kirim: `OTP_EMAIL=SENT`, `RC_EXTERNAL_VERIFY=OK`, exit 0.
- Terima: `rc-otp-consume.php` dengan kode benar → `OTP_CONSUME=OK verified=yes`, exit 0; kode salah → exit 1.
- SMTP tak terjangkau → `OTP_EMAIL=FAIL class=Symfony\Component\HttpKernel\Exception\HttpException`, `RC_EXTERNAL_VERIFY=FAIL otp_email`, exit 1.
- `RC_OTP_TEST_EMAIL` kosong → `OTP_EMAIL=SKIPPED`, `RC_EXTERNAL_VERIFY=FAIL otp_email_no_recipient`, exit 1.

Ini membuktikan jalur kirim/verifikasi dan gerbang exit code berfungsi, **bukan** bukti pengiriman ke penerima nyata. Bukti email nyata tetap memerlukan `RC_OTP_TEST_EMAIL` milik penguji pada file env kandidat privat.

## Ruang lingkup persetujuan deployment (satu keputusan)

Setelah `RC_OTP_TEST_EMAIL` diisi dan `OTP_EMAIL=SENT` terverifikasi, persetujuan tunggal mencakup: (1) commit dan push branch RC kedua repo (backend `aquara_proyek2`, mobile `aquara_mobile`) berisi seluruh perubahan lokal yang ada saat ini; (2) provisioning berbayar Helipod Apps & Containers paket Micro (estimasi Rp125.250/bulan, konfirmasi harga saat checkout); (3) deploy backend dari GitHub, set secrets (nama variabel saja: APP_KEY, APP_URL, DB_*, CLOUDINARY_*, MAIL_*, FONNTE_TOKEN, GROQ_API_KEY, GOMBYANG_PROVIDER=groq), migrasi sekali, verifikasi /up, HTTPS, Filament; (4) build APK release universal dengan SAMUDERA_API_URL dari origin terverifikasi, verifikasi apksigner + SHA-256, URL unduhan, QR code; (5) uji unduh, instal, login, akses API dari HP fisik di jaringan berbeda. APK gladi 4002 tetap sebagai cadangan.

## Daftar perubahan Git final (branch RC) — disiapkan 24 November 2026

Belum ada commit/push. Dua repo pada branch `main` dengan perubahan lokal; baseline backend `76a00cb`, mobile `91ad816`.

### Backend `aquara_proyek2`

Sudah dilacak (`M`): `Dockerfile`, `.dockerignore`, `.env.example`, `composer.json`, `composer.lock`, `phpunit.xml`, `routes/api.php`, `routes/console.php`, `config/backup.php`, `config/filesystems.php`, `config/services.php`, `database/seeders/DatabaseSeeder.php`, `app/Providers/AppServiceProvider.php`, `app/Providers/Filament/AdminPanelProvider.php`, `app/Models/{User,Pasar,FishType,CommunityPost,CommunityComment}.php`, `app/Http/Controllers/Api/{Auth,Pasar,Forum,Notification,AiConsultation}Controller.php`, `app/Filament/Resources/{AiConsultation,FishType,Pasar,Post}Resource.php`, `app/Filament/Widgets/OverviewStats.php`, `tests/Feature/SemuaHalamanBisaDiAksesTest.php`.

Baru (`??`): dokumentasi (`AGENTS.md`, `ARCHITECTURE.md`, `PRD.md`, `TODO.md`, `workflow.md`, `docs/`), `app/Http/Controllers/Api/{Otp,PreOrder,RetailOrder,Search,ContentDetail,ForumComment,Profile,LaporDarurat,AsistenChat,ExternalInfo}Controller.php`, `app/Http/Resources/`, `app/Models/{PreOrder,RetailOrder,TpiVerification,LaporDarurat,AsistenChat,CommentLike}.php`, `app/Policies/{PreOrder,LaporDarurat}Policy.php`, `app/Support/`, `app/Traits/`, `app/Filament/Resources/{PreOrder,LaporDarurat,TpiVerification}Resource.php` beserta subdirektori, `app/Filament/Widgets/{InfoCuaca,InfoKkp,PetaTpi}Widget.php`, `config/{cloudinary,admin_seed,cors}.php`, `docker/rc-start.sh` + `docker/rc-supervisor.conf`, `public/images/SAMUDERA_FIX_LOGO.{jpg,png}`, 10 migrasi (katalog Pasar, lapor_darurat, pre_orders, asisten_chats, otp_challenges, comments threads/likes, is_mock, seller confirmation, tpi_verifications, retail_orders+unit), `tests/Feature/` (14 berkas: `CandidateMariaDbTest`, `LaporDaruratTest`, `PreOrderTest`, `ForumCommentsTest`, `OmniSearchTest`, `ContentDetailTest`, `UserProfileTest`, `AsistenChatTest`, `ExternalInfoTest`, `BmkgForecastTest`, `SecurityAuditTest`, `SecurityHardeningTest`, `AuthRateLimiterHttpTest`, `ForumMysqlIntegrationTest`), `tests/Unit/AuthRateLimiterTest.php`, `tests/Support/` (termasuk `rc-external-verify.php`, `rc-otp-consume.php`, `run-candidate.ps1`, `UsesIsolatedApplication.php`, helper MySQL).

Perubahan sesi ini hanya `tests/Support/rc-external-verify.php` (untracked): daftar migrasi OTP kandidat yang benar, verifikasi skema per kolom, dan exit code non-zero.

### Mobile `aquara_mobile`

Sudah dilacak (`M`): `pubspec.yaml`, `pubspec.lock`, `lib/main.dart`, `lib/services/{api_client,api_service,forum_api}.dart`, 24 layar `lib/screens/*`, 3 model (`forum_post`, `pasar_item`, `sos_report`), `android/app/build.gradle.kts`, `android/app/src/main/AndroidManifest.xml`, `ios/Runner/Info.plist`, seluruh resource launcher/splash Android, registran plugin `macos/`+`windows/`, 9 berkas `test/*`, dokumentasi `docs/{API_DOCUMENTATION,Asisten_SAMUDERA,FLUTTER_UPGRADE_PLAN}.md`, `AGENTS.MD`, `PRD.md`, `TODO.md`.

Baru (`??`): `lib/main_sos_demo.dart`, `lib/services/{asisten,external_info,omni_search,pre_order,profile,retail}_api.dart`, 8 layar baru (`asisten_samudera`, `external_info`, `forum_comments`, `icon_credits`, `member_profile`, `perizinan`, `pre_order`, `retail`), 5 model baru, 18 widget baru (`samudera_theme`, `samudera_branding`, `gombyang`, `maritime_weather`, `weather_forecast`, `tpi_map`, `omni_results`, `home_*`, `profile_*`, `service_discovery`, `support_banners`, `commodity_card`, `curated_info`, `feature_tabs`, `app_version_label`), aset `assets/images/`, `assets/icons/` (termasuk berkas brand Instagram/WhatsApp resmi), splash `samudera_splash.png` per densitas, `android/.../res/values-v31` + `values-night-v31`, 17 berkas `test/*` baru, dokumentasi `docs/{ASET_KONSULTASI_RESMI,PERIZINAN_SAMUDERA,UI_BERANDA_SAMUDERA}.md` dan `docs/{beranda-2d,cuaca-maritim}/`.

Catatan: daftar ini diturunkan dari `git status` 24 November 2026 dan **tidak** memuat hash/isi berkas; tinjau sebelum commit dan jangan push worktree secara massal.

## Persetujuan deployment Helipod Micro — status gerbang

Siap dieksekusi **hanya setelah** `RC_OTP_TEST_EMAIL` penguji diisi, `OTP_EMAIL=SENT` terverifikasi pada file env kandidat privat, dan `RC_EXTERNAL_VERIFY=OK` (exit 0). Sampai itu terjadi, status: **belum disetujui, menunggu**. Persetujuan tunggal tetap mencakup lima butir pada bagian "Ruang lingkup persetujuan deployment (satu keputusan)" di atas; tidak ada otorisasi belanja, commit, push, atau deploy dari sesi ini.


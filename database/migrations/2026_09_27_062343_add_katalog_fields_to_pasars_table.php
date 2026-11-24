<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom Katalog Produk (Fase 1 - PRD SAMUDERA) ke tabel `pasars`.
     * Tipe kolom dipilih agar aman/portable untuk PostgreSQL (Heroku Postgres):
     * - `stok`            : integer, jumlah ketersediaan komoditas.
     * - `kategori`        : string (bukan native enum Postgres) agar mudah di-ALTER
     *                       di masa depan. Nilai valid divalidasi di layer aplikasi
     *                       (Controller & Filament Select): tawar | laut | olahan.
     * - `tipe_penjualan`  : string, nilai valid: eceran | grosir.
     */
    public function up(): void
    {
        Schema::table('pasars', function (Blueprint $table) {
            $table->integer('stok')->default(0)->after('harga');
            $table->string('kategori')->default('tawar')->after('stok');
            $table->string('tipe_penjualan')->default('eceran')->after('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasars', function (Blueprint $table) {
            $table->dropColumn(['stok', 'kategori', 'tipe_penjualan']);
        });
    }
};

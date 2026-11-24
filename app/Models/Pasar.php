<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pasar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_ikan',
        'deskripsi',
        'harga',
        'foto',
        'nomor_wa',
        'lokasi',
        'stok',
        'kategori',
        'tipe_penjualan',
    ];

    /**
     * Daftar nilai valid untuk kolom `kategori`.
     * Divalidasi di layer aplikasi (bukan native Postgres ENUM) agar portable.
     */
    public const KATEGORI_OPTIONS = [
        'tawar' => 'Ikan Air Tawar',
        'laut' => 'Ikan Laut',
        'olahan' => 'Olahan',
    ];

    /**
     * Daftar nilai valid untuk kolom `tipe_penjualan`.
     */
    public const TIPE_PENJUALAN_OPTIONS = [
        'eceran' => 'Eceran',
        'grosir' => 'Grosir',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
            'stok' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi Bursa Grosir: seluruh pre-order yang masuk ke komoditas ini.
     */
    public function preOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PreOrder::class, 'pasar_id');
    }
}
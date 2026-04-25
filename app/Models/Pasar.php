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
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
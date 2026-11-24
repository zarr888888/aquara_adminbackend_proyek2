<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporDarurat extends Model
{
    public const JENIS_SOS_OPTIONS = [
        'Wabah Penyakit' => 'Wabah Penyakit',
        'Pencemaran Limbah' => 'Pencemaran Limbah',
        'Cuaca Ekstrem' => 'Cuaca Ekstrem',
    ];

    public const STATUS_OPTIONS = [
        'Pending' => 'Pending',
        'Diproses' => 'Diproses',
        'Selesai' => 'Selesai',
    ];

    public const ADMIN_ROLES = ['Admin Dinas', 'super_admin'];

    protected $fillable = [
        'user_id', 'jenis_sos', 'deskripsi', 'foto', 'status_laporan', 'admin_id',
    ];

    protected $attributes = ['status_laporan' => 'Pending'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
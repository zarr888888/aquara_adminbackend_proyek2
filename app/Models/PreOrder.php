<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreOrder extends Model
{
    public const STATUS_OPTIONS = ['Belum Bayar' => 'Belum Bayar', 'DP' => 'DP', 'Lunas' => 'Lunas'];

    public const ADMIN_ROLES = ['Admin Dinas', 'super_admin'];

    // Ownership and payment status must never come from mass assignment.
    protected $fillable = ['pasar_id', 'total_tonase', 'tgl_pengambilan'];

    protected $attributes = ['status_bayar' => 'Belum Bayar'];

    protected function casts(): array
    {
        return ['total_tonase' => 'decimal:2', 'tgl_pengambilan' => 'date:Y-m-d'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pasar(): BelongsTo
    {
        return $this->belongsTo(Pasar::class);
    }

    public function scopeForParticipant(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereHas('pasar', fn (Builder $pasar) => $pasar->where('user_id', $user->id));
        });
    }
}
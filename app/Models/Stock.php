<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'fish_type_id', 
        'pengepul_name', 
        'location', 
        'amount_kg', 
        'phone_number'
    ];

    public function fishType(): BelongsTo
    {
        return $this->belongsTo(FishType::class, 'fish_type_id');
    }
}
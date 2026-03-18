<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'fish_type_id',
        'pengepul_name',
        'location',
        'amount_kg',
        'phone_number',
    ];

    public function fishType()
    {
        return $this->belongsTo(FishType::class, 'fish_type_id');
    }
}
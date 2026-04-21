<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class FishType extends Model
{
    protected $fillable = [
        'name',
        'image',
        'fcr_ratio',
        'harvest_duration_days',
        'market_price_per_kg',
        'standard_density_per_m2', 
        'survival_rate',
        'feed_price_per_kg',
    ];

    protected $appends = ['image_url'];

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image ? url('storage/' . $this->image) : null,
        );
    }
}
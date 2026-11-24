<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetailOrder extends Model
{
    protected $casts = ['jumlah'=>'decimal:3','harga_satuan'=>'integer','total_harga'=>'integer','confirmed_at'=>'datetime'];
}
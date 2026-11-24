<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TpiVerification extends Model
{
    public const FACILITIES = ['dadap'=>'TPI Dadap', 'glayem'=>'TPI Glayem', 'karangsong'=>'TPI Karangsong', 'eretan-wetan'=>'TPI Eretan Wetan (Misaya Mina)', 'mina-bahari'=>'TPI Mina Bahari'];
    protected $guarded = ['id', 'verified_by'];
    protected $casts = ['published'=>'boolean', 'lat'=>'float', 'lng'=>'float'];

    protected static function booted(): void
    {
        static::creating(function (self $row) {
            abort_unless(auth()->user()?->hasAnyRole(['Admin Dinas', 'super_admin']), 403);
            $row->verified_by = auth()->id();
            Validator::make($row->toArray(), [
                'facility'=>['required', Rule::in(array_keys(self::FACILITIES))], 'published'=>['required','boolean'],
                'evidence'=>['required','string','min:12','max:2000'],
                'lat'=>['required_if:published,1','nullable','numeric','between:-90,90'],
                'lng'=>['required_if:published,1','nullable','numeric','between:-180,180'],
                'source_url'=>['required_if:published,1','nullable','url:https','max:2048', 'regex:~^https://(?:[a-z0-9-]+\.)*(?:kkp\.go\.id|indramayukab\.go\.id)/[^\s]+$~i'],
            ])->validate();
            if (!$row->published) { $row->lat = $row->lng = $row->source_url = null; }
        });
        static::created(fn () => Cache::forget('samudera:external-info:curated:v1:tpi'));
    }
}
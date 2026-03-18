<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CommunityPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'author_name',
        'content',
        'image',
        'likes_count',
    ];

    public function comments()
    {
        return $this->hasMany(CommunityComment::class)->latest(); 
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    protected static function boot() {
        parent::boot();
        static::deleting(function($post) {
            if ($post->image) { Storage::disk('public')->delete($post->image); }
        });
    }
}